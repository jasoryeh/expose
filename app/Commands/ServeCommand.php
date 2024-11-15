<?php

namespace App\Commands;

use App\Server\Factory;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;

class ServeCommand extends ExposeCommand
{
    protected $signature = 'serve {hostname=localhost} {host=0.0.0.0}  {--validateAuthTokens} {--port=8080} {--config=}';

    protected $description = 'Start the expose server';

    protected function loadConfiguration(string $configFile)
    {
        $configFile = realpath($configFile);

        throw_if(! file_exists($configFile), new InvalidArgumentException("Invalid config file {$configFile}"));

        $localConfig = require $configFile;
        config()->set('expose', $localConfig);
    }

    public function handle()
    {
        /** @var LoopInterface $loop */
        $loop = app(LoopInterface::class);

        if ($this->option('config')) {
            $this->loadConfiguration($this->option('config'));
        }

        $loop->futureTick(function () {
            $name = config('app.name');
            $ver = config('app.version');
            $env = config('app.env');
            $lang = config('app.locale');
            $config = $this->option('config') ?? 'default';
            $host = $this->argument('host');
            $port = $this->option('port');
            $hostname = $this->argument('hostname');
            $admin = config('expose.admin.subdomain');
            $database = config('expose.admin.database');
            $this->info("{$name} v{$ver}-{$lang} [{$env}]");
            $this->info("Database: {$database}");
            $this->info("Configuration: {$config}");
            $this->info("Hostname set to {$hostname}");
            $this->info("Administrator interface accessible at {$admin}");
            $this->info("Server running on port {$port} on host {$host}");
        });

        $validateAuthTokens = config('expose.admin.validate_auth_tokens');

        if ($this->option('validateAuthTokens') === true) {
            $validateAuthTokens = true;
        }

        (new Factory())
            ->setLoop($loop)
            ->setHost($this->argument('host'))
            ->setPort($this->option('port'))
            ->setHostname($this->argument('hostname'))
            ->validateAuthTokens($validateAuthTokens)
            ->createServer()
            ->run();
    }
}
