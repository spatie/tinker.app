<?php

namespace App\WebSockets;

use App\Services\Docker\Container;
use App\Services\Docker\ContainerRepository;
use PartyLine;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class ContainerConnection
{
    /** @var \Ratchet\ConnectionInterface */
    protected $browserConnection;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    /** @var \App\Services\Docker\Container */
    protected $container;

    public function __construct(ConnectionInterface $browserConnection, string $sessionId, LoopInterface $loop)
    {
        $this->browserConnection = $browserConnection;

        $this->loop = $loop;

        $this->container = $this->findOrCreateContainer($sessionId, $browserConnection);

        if ($this->container) {
            $this->bindContainer($browserConnection);
        }
    }

    public function usesBrowserConnection(ConnectionInterface $browserConnection): bool
    {
        return $this->browserConnection === $browserConnection;
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

    protected function findOrCreateContainer(string $sessionId, ConnectionInterface $browserConnection): ?Container
    {
        if ($sessionId) {
            return $this->findContainer($sessionId, $browserConnection);
        }

        $container = (Container::create($this->loop))->start();

        $browserConnection->send("New container created ({$container->getName()})\n\r");

        return $container;
    }

    protected function findContainer(string $sessionId, ConnectionInterface $browserConnection): ?Container
    {
        $container = (new ContainerRepository($this->loop))->findBySessionId($sessionId);

        if (!$container) {
            $browserConnection->send("Session id `{$sessionId}` is invalid.\n\r");
            $browserConnection->close();

            return null;
        }

        $browserConnection->send("Session id `{$sessionId}` found.\n\r");

        return $container;
    }

    protected function bindContainer(ConnectionInterface $browserConnection)
    {
        $this->container->onMessage(function ($message) use ($browserConnection) {
            $browserConnection->send((string) $message);
        });

        $this->container->onClose(function () use ($browserConnection) {
            PartyLine::error("Connection to container lost; closing browser connection {$browserConnection->resourceId}");

            $browserConnection->close();
        });
    }
}
