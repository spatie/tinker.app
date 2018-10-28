<?php

namespace App\Console;

use App\WebSockets\Commands\StartWebSocketServerCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        StartWebSocketServerCommand::class,
    ];
}
