<?php

namespace App\WebSockets;

use Illuminate\Support\ServiceProvider;

class WebSocketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WebSocketConnectionRepository::class);
    }
}
