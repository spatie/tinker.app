<?php

namespace App\WebSockets;

use App\Services\Docker\Container;
use PartyLine;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class Client
{
    /** @var \App\Services\Docker\Container */
    protected $container;

    /** @var \Ratchet\ConnectionInterface */
    protected $browserConnection;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct(ConnectionInterface $browserConnection, LoopInterface $loop)
    {
        $this->browserConnection = $browserConnection;

        $this->loop = $loop;
    }

    public function usesBrowserConnection(ConnectionInterface $browserConnection): bool
    {
        return $this->browserConnection === $browserConnection;
    }

    public function attachContainer(Container $container): self
    {
        $this->container = $container;

        $this->container->onMessage(function ($message) {
            $this->browserConnection->send((string) $message);
        });

        $this->container->onClose(function ($message) {
            PartyLine::error("Connection to container lost; closing browser connection {$this->browserConnection->resourceId}");

            $this->browserConnection->close();
        });

        return $this;
    }

    public function sendToContainer(string $message)
    {
        $this->container->sendMessage($message);
    }

    public function cleanupContainer()
    {
        $this
            ->container
            ->stop()
            ->remove();
    }
}
