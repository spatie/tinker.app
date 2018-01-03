<?php

namespace App\Console\Commands;

use App\WebSockets\Controllers\TinkerController;
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

        $host = config('websockets.host');
        $port = config('websockets.port');
        $allowedOrigins = config('websockets.allowedOrigins');

        $ioServer = new App($host, config('websockets.port'), '0.0.0.0', $loop);

        // $ioServer->route('', new TinkerController($loop), config('websockets.allowedOrigins'));

        $decoratedController = new WsServer(new TinkerController($loop));
        $decoratedController->enableKeepAlive($loop);
        $decoratedController = new OriginCheck($decoratedController, $allowedOrigins);

        foreach ($allowedOrigins as $allowedOrgin) {
            $ioServer->flashServer->app->addAllowedAccess($allowedOrgin, $port);
        }

        $ioServer->routes->add('tinker', new Route('/{sessionId}', ['_controller' => $decoratedController, 'sessionId' => null], ['Origin' => $host], [], $host, [], ['GET']));

        $this->info("WebSocket server started on {$host}:{$port}");
        $this->comment('Allowed origins: '.implode(', ', $allowedOrigins));

        $ioServer->run();
    }
}
