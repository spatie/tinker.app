<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __invoke(string $sessionId = '')
    {
        return view('home', compact('sessionId'));
    }
}
