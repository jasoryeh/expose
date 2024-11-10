<?php

namespace App\Commands;

use App\Client\Exceptions\InvalidServerProvided;
use App\Logger\CliRequestLogger;
use App\Server\Exceptions\ExposeServerException;
use Illuminate\Console\Parser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class ServerAwareCommand extends ExposeCommand
{

    public function __construct()
    {
        parent::__construct();

        $inheritedSignature = '{--server=} {--server-host=} {--server-port=}';

        $this->getDefinition()->addOptions(Parser::parse($inheritedSignature)[2]);

        $this->configureConnectionLogger();
    }

    protected function configureConnectionLogger()
    {
        app()->singleton(CliRequestLogger::class, function () {
            return new CliRequestLogger(new ConsoleOutput());
        });

        return $this;
    }

    protected function getServerHost()
    {
        if ($this->option('server-host')) {
            return $this->option('server-host');
        }

        if (config('expose.servers') === null) {
            throw new ExposeServerException("'expose.servers' is not set!");
        }

        $server = $this->option('server') ?? config('expose.default_server');
        $host = config('expose.servers.'.$server.'.host');

        if (! is_null($host)) {
            return $host;
        }

        return $this->lookupRemoteServerHost($server);
    }

    protected function getServerPort()
    {
        if ($this->option('server-port')) {
            return $this->option('server-port');
        }

        if (config('expose.servers') === null) {
            throw new ExposeServerException("'expose.servers' is not set!");
        }

        $server = $this->option('server') ?? config('expose.default_server');
        $host = config('expose.servers.'.$server.'.port');

        if (! is_null($host)) {
            return $host;
        }

        return $this->lookupRemoteServerPort($server);
    }

    protected function lookupRemoteServers()
    {
        if (config('expose.server_endpoint') === null) {
            throw new ExposeServerException("'expose.server_endpoint' is not set!");
        }

        try {
            return Http::withOptions([
                'verify' => false,
            ])->get(config('expose.server_endpoint'))->json();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function lookupRemoteServerHost($server)
    {
        $servers = $this->lookupRemoteServers();
        $host = Arr::get($servers, $server.'.host');

        throw_if(is_null($host), new InvalidServerProvided($server));

        return $host;
    }

    protected function lookupRemoteServerPort($server)
    {
        $servers = $this->lookupRemoteServers();
        $port = Arr::get($servers, $server.'.port');

        throw_if(is_null($port), new InvalidServerProvided($server));

        return $port;
    }
}
