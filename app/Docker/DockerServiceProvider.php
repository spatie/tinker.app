<?php

namespace App\Docker;

use Docker\Docker;
use Illuminate\Support\ServiceProvider;

class DockerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton(Docker::class, function ($app) {
            return Docker::create();
        });

        $this->app->singleton(ContainerRepository::class);
    }
}
