<?php

namespace App\WebSockets\Commands;

use App\WebSockets\BrowserEventHandler;
use App\WebSockets\TinkerServer;
use App\WebSockets\WebSocketEventHandler;
use Illuminate\Console\Command;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Wamp\WampServer;
use Wilderborn\Partyline\Facade as Partyline;
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

    public function handle()
    {
        Partyline::bind($this);

        $loop = Factory::create();

        $host = config('websockets.host');
        $port = config('websockets.port');

        Partyline::info("WebSocket server started on {$host}:{$port} allowing access from localhost only.");

        $app = new App($host, $port, '127.0.0.1', $loop);

        $app->route('/ws', new TinkerServer($loop), ['*']);

        $app->run();
    }
}
