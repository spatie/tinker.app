<?php

namespace App\Services;

use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class Client
{
    public $tinkerContainer;

    public function __construct(ConnectionInterface $conn, LoopInterface $loop)
    {
        $this->tinkerContainer = new TinkerContainer();
        $this->tinkerContainer->start();
        $this->tinkerContainer->onMessage($loop, function ($message) use ($conn) {
            $conn->send((string) $message);
        });
    }
}
