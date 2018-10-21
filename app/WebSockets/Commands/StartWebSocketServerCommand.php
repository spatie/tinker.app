<?php

namespace App\WebSockets\Commands;

use App\WebSockets\TinkerServer;
use Illuminate\Console\Command;
use React\EventLoop\LoopInterface;
use Wilderborn\Partyline\Facade as Partyline;
use Ratchet\App;
use React\EventLoop\Factory;

class StartWebSocketServerCommand extends Command
{
    protected $signature = 'start-websocket-server';

    protected $description = 'Start the browser facing websocket connection';

    public function handle()
    {
        Partyline::bind($this);

        $loop = Factory::create();

        $this->getLaravel()->singleton(LoopInterface::class, $loop);

        $host = config('websockets.host');
        $port = config('websockets.port');

        Partyline::info("WebSocket server started on {$host}:{$port} allowing access from localhost only.");

        $app = new App($host, $port, '127.0.0.1', $loop);

        $app->route('/ws', new TinkerServer($loop), ['*']);

        $app->run();
    }
}
