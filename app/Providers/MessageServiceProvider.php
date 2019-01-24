<?php

namespace App\Providers;

use App\WebSockets\Handlers\RunCodeBufferHandler;
use App\WebSockets\Handlers\SessionStartHandler;
use App\WebSockets\Handlers\TerminalDataHandler;
use App\WebSockets\Handlers\UpdateCodeBufferHandler;
use App\WebSockets\Message;
use App\WebSockets\MessageDispatcher;
use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(MessageDispatcher::class);
    }
    
    public function boot(MessageDispatcher $dispatcher)
    {
        $dispatcher->handle(Message::SESSION_START_TYPE, SessionStartHandler::class);
        $dispatcher->handle(Message::TERMINAL_DATA_TYPE, TerminalDataHandler::class);
        $dispatcher->handle(Message::BUFFER_RUN_TYPE, RunCodeBufferHandler::class);
        $dispatcher->handle(Message::BUFFER_CHANGE_TYPE, UpdateCodeBufferHandler::class);
    }
}
