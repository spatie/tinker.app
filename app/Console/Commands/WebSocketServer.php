<?php

namespace App\Console\Commands;

use App\WebSockets\Controllers\TinkerController;
use Illuminate\Console\Command;
use League\Uri\Schemes\Ws;
use Ratchet\App;
use Ratchet\Http\OriginCheck;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use Symfony\Component\Routing\Route;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:init';

    protected $description = 'Start the browser faceing websocket connection';

    /** @var \Ratchet\WebSocket\WsServer */
    protected $websocketServer;

    public function handle()
    {
        $eventLoop = Factory::create();

        $ratchetApp = new App(
            config('websockets.host'),
            config('websockets.port'),
            '0.0.0.0',
            $eventLoop
        );

        $this->websocketServer = new WsServer(new TinkerController($eventLoop));
        $this->websocketServer->enableKeepAlive($eventLoop);

        $this->addAllowedOrigins($ratchetApp);

        $ratchetApp->routes->add('tinker', $this->getRoute());

        $this->info('WebSocket server started on ' . config('websockets.host') . ':' . config('websockets.host'));
        $this->comment('Allowed origins: '.implode(', ', config('websockets.allowedOrigins')));

        $ratchetApp->run();
    }

    protected function addAllowedOrigins(App $ratchetApp)
    {
        $allowedOrigins = config('websockets.allowedOrigins');
        $port = config('websockets.port');

        $this->websocketServer = new OriginCheck($this->websocketServer, $allowedOrigins);

        foreach ($allowedOrigins as $allowedOrgin) {
            $ratchetApp->flashServer->app->addAllowedAccess($allowedOrgin, $port);
        }
    }

    protected function getRoute(): Route
    {
        return new Route('/{sessionId}', [
            '_controller' => $this->websocketServer,
            'sessionId' => null],
            ['Origin' => config('websockets.host')],
            [],
            config('websockets.host'),
            [],
            ['GET']);
    }
}
