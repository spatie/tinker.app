<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __invoke(?string $sessionId = null)
    {
        return view('home', compact('sessionId'));
    }
}
