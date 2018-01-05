<?php

namespace App\Console;

use App\WebSockets\Commands\StartWebSocketServer;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        StartWebSocketServer::class,
    ];
}
