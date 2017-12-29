<?php

namespace App\Services;

use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class Client
{
    /** @var \SplObjectStorage  */
    public $clients;

    public $tinkerContainer;

    public function __construct(ConnectionInterface $conn, LoopInterface $loop)
    {
        $this->clients = new \SplObjectStorage();

        $this->tinkerContainer = new TinkerContainer();
        $this->tinkerContainer->start();
        $this->tinkerContainer->onMessage($loop, function ($message) use ($conn) {
            $conn->send((string) $message);
        });

        $this->clients->attach($conn);
    }
}
