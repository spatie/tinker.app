<?php

namespace App\Console\Commands;

use App\WebSockets\Controllers\DockerController;
use Illuminate\Console\Command;
use Ratchet\App;
use Ratchet\Http\OriginCheck;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use Symfony\Component\Routing\Route;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:init';

    protected $description = 'Command description';

    public function handle()
    {
        $loop = Factory::create();

        $httpHost = config('websockets.host');

        $ioServer = new App($httpHost, config('websockets.port'), '0.0.0.0', $loop);

        // $ioServer->route('/{sessionId}', new DockerController($loop), config('websockets.allowedOrigins'));

        $decoratedController = new WsServer(new DockerController($loop));
        $decoratedController->enableKeepAlive($loop);
        $decoratedController = new OriginCheck($decoratedController, config('websockets.allowedOrigins'));

        $ioServer->routes->add('tinker', new Route('/{sessionId}', ['_controller' => $decoratedController, 'sessionId' => null], ['Origin' => $httpHost], [], $httpHost, [], ['GET']));

        $ioServer->run();
    }
}
