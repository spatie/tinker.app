<?php

namespace App\Docker;

use Illuminate\Support\ServiceProvider;

class DockerServiceProvider extends ServiceProvider
{
    /** @var array */
    public $singletons = [
        ContainerRepository::class => ContainerRepository::class,
    ];

    public function boot()
    {
        //
    }

    public function register()
    {
        //
    }
}
