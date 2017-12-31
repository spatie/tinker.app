<?php

namespace App\Services;

use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class Client
{
    /** @var \App\Services\Docker\TinkerContainer */
    protected $tinkerContainer;

    /** @var \Ratchet\ConnectionInterface */
    protected $connection;

    public function __construct(ConnectionInterface $connection, LoopInterface $loop)
    {
        $this->connection = $connection;

        $this->tinkerContainer = new TinkerContainer($loop);

        $this->tinkerContainer->start();

        $this->tinkerContainer->onMessage(function ($message) use ($connection) {
            $connection->send((string) $message);
        });

        $this->tinkerContainer->onQuit(function ($message) use ($connection) {
            $connection->close();
        });
    }

    public function cleanupContainer()
    {
        $this
            ->tinkerContainer
            ->stop()
            ->remove();
    }

    public function sendToTinker(string $message)
    {
        $this->tinkerContainer->sendToWebSocket($message);
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}
