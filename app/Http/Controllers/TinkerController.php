<?php

namespace App\Http\Controllers;

class TinkerController extends Controller
{
    public function __invoke(string $sessionId = '')
    {
        return view('terminal', compact('sessionId'));
    }
}
