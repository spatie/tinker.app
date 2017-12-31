<?php

namespace App\Console\Commands;

use App\WebSockets\Controllers\DockerController;
use Illuminate\Console\Command;
use Ratchet\App;
use React\EventLoop\Factory;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:init';

    protected $description = 'Command description';

    public function handle()
    {
        $loop = Factory::create();

        $wsServer = new App(env('WS_HOST'), 8080, '0.0.0.0', $loop);

        $wsServer->route('', new DockerController($loop), ['*']);

        $wsServer->run();
    }
}
