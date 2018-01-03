<?php

namespace App\Http\Controllers;

class TinkerController extends Controller
{
    public function __invoke(?string $sessionId = null)
    {
        if (! $sessionId) {
        }

        return view('terminal');
    }
}
