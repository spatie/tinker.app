<?php

namespace App\WebSockets\Commands;

use App\WebSockets\BrowserEventHandler;
use App\WebSockets\WebSocketEventHandler;
use Illuminate\Console\Command;
use Partyline;
use Ratchet\App;
use Ratchet\Http\OriginCheck;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\EventLoop\StreamSelectLoop;
use Symfony\Component\Routing\Route;

class StartWebSocketServer extends Command
{
    protected $signature = 'start-websocket-server';

    protected $description = 'Start the browser facing websocket connection';

    /** @var \Ratchet\WebSocket\WsServer */
    protected $webSocketServer;

    public function handle()
    {
        Partyline::bind($this);

        $eventLoop = Factory::create();

        $ratchetApp = new App(
            config('websockets.host'),
            config('websockets.port'),
            '0.0.0.0',
            $eventLoop
        );

        $this->configureWebSocketServer($eventLoop, $ratchetApp);

        $ratchetApp->routes->add('tinker', $this->getRoute());

        $this->outputStartedMessageToConsole();

        $ratchetApp->run();
    }

    public function configureWebSocketServer(StreamSelectLoop $eventLoop, App $ratchetApp)
    {
        $this->webSocketServer = new WsServer(new WebSocketEventHandler($eventLoop, new BrowserEventHandler($eventLoop)));

        $this->webSocketServer->enableKeepAlive($eventLoop);

        $this->addAllowedOrigins($ratchetApp);
    }

    protected function addAllowedOrigins(App $ratchetApp)
    {
        $allowedOrigins = config('websockets.allowedOrigins');
        $port = config('websockets.port');

        $this->webSocketServer = new OriginCheck($this->webSocketServer, $allowedOrigins);

        foreach ($allowedOrigins as $allowedOrgin) {
            $ratchetApp->flashServer->app->addAllowedAccess($allowedOrgin, $port);
        }
    }

    protected function getRoute(): Route
    {
        return new Route(
            '/{sessionId}',
            [
                '_controller' => $this->webSocketServer,
                'sessionId' => null
            ],
            ['Origin' => config('websockets.host')],
            [],
            config('websockets.host'),
            [],
            ['GET']
        );
    }

    public function outputStartedMessageToConsole()
    {
        $wsConfig = config('websockets');
        $allowedOrigins = implode(', ', $wsConfig['allowedOrigins']);

        Partyline::info("WebSocket server started on {$wsConfig['host']}:{$wsConfig['port']}");
        Partyline::comment("Allowed origins: {$allowedOrigins}");
    }
}
