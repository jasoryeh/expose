<?php

namespace App\Commands;

use App\Client\Factory;
use React\EventLoop\LoopInterface;

class SharePortCommand extends ServerAwareCommand
{
    protected $signature = 'share-port {port} {--auth=}';

    protected $description = 'Share a local port with a remote expose server';

    public function handle()
    {
        $auth = $this->getToken();

        (new Factory())
            ->setLoop(app(LoopInterface::class))
            ->setHost($this->getServerHost())
            ->setPort($this->getServerPort())
            ->setAuth($auth)
            ->createClient()
            ->sharePort($this->argument('port'))
            ->createHttpServer()
            ->run();
    }
}
