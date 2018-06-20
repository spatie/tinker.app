<?php

namespace App\Http\Controllers;

use App\Services\Docker\Container;
use React\EventLoop\Factory;

class HomeController extends Controller
{
    public function startSession()
    {
        if (! request()->get('start')) {
            return '<a href="?start=true">start</a>';
        }

        $container = Container::create(Factory::create());
        $container->start();

        $sessionId = $container->getName();

        return redirect('/'.$sessionId);
    }

    public function joinSession(string $sessionId)
    {
        return view('home', compact('sessionId'));
    }
}
