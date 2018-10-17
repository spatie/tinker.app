<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Docker\Container;
use App\Models\Container as ContainerModel;
use React\EventLoop\Factory;

class TinkerSessionsController extends Controller
{
    public function start()
    {
        $container = Container::create(Factory::create());
        $container->start();

        $containerName = $container->getName();

        return [
            'id' => $containerName,
            'code' => 'echo "default example";',
        ];
    }

    public function load(string $sessionId)
    {
        $container = ContainerModel::findByName($sessionId);

        abort_unless($container, 404, 'Session not found');

        return [
            'id' => $container->name,
            'code' => $container->code,
        ];
    }
}
