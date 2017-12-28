<?php

namespace App\Console\Commands;

use App\WebSockets\Controllers\DockerController;
use Illuminate\Console\Command;
use Ratchet\App;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:init';

    protected $description = 'Command description';

    public function handle()
    {
        $wsServer = new App('localhost', 8080, '0.0.0.0', null);

        $wsServer->route('', new DockerController(), ['*']);

        $wsServer->run();
    }
}
