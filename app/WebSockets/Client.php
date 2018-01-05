<?php

namespace App\WebSockets;

use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use PartyLine;

class Client
{
    /** @var \App\Services\Docker\TinkerContainer */
    protected $tinkerContainer;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    /** @var \Ratchet\ConnectionInterface */
    protected $connection;

    public function __construct(ConnectionInterface $connection, LoopInterface $loop)
    {
        $this->connection = $connection;

        $this->loop = $loop;
    }

    public function attachContainer(TinkerContainer $tinkerContainer): self
    {
        $this->tinkerContainer = $tinkerContainer;

        $this->tinkerContainer->onMessage(function ($message) {
            $this->connection->send((string) $message);
        });

        $this->tinkerContainer->onClose(function ($message) {
            PartyLine::error("Connection to container lost; closing websocket to client {$this->connection->resourceId}");

            $this->connection->close();
        });

        return $this;
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
