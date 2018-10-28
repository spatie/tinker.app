<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class EventLoopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->instance(LoopInterface::class, Factory::create());
    }
}
