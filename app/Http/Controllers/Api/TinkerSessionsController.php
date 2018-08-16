<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Docker\Container;
use React\EventLoop\Factory;

class TinkerSessionsController extends Controller
{
    public function start()
    {
        $container = Container::create(Factory::create());
        $container->start();

        $sessionId = $container->getName();

        return [
            'sessionId' => $sessionId
        ];
    }
}
