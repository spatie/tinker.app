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

    public function __construct(ConnectionInterface $browserConnection, LoopInterface $loop, ?string $sessionId = null)
    {
        $this->browserConnection = $browserConnection;

        $this->loop = $loop;

        $this->container = $this->findOrCreateContainer($browserConnection, $sessionId);

        if ($this->container) {
            $this->bindContainer($browserConnection);
        }
    }

    public function usesBrowserConnection(ConnectionInterface $browserConnection): bool
    {
        return $this->browserConnection === $browserConnection;
    }

    public function sendMessage(string $message): self
    {
        $this->container->sendMessage($message);

        return $this;
    }

    public function close()
    {
        $this
            ->container
            ->stop()
            ->remove();
    }

    protected function findOrCreateContainer(ConnectionInterface $browserConnection, ?string $sessionId = null): ?Container
    {
        if ($sessionId) {
            return $this->findContainer($sessionId, $browserConnection);
        }

        $container = (Container::create($this->loop))->start();

        $browserConnection->send(
            Message::terminalData("New container created ({$container->getName()})\n\r")
        );

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

        $browserConnection->send(
            Message::terminalData("Session id `{$sessionId}` found.\n\r")
        );

        return $container;
    }

    protected function bindContainer(ConnectionInterface $browserConnection)
    {
        $this->container->onMessage(function ($message) use ($browserConnection) {
            $browserConnection->send(Message::terminalData((string) $message));
        });

        $this->container->onClose(function () use ($browserConnection) {
            PartyLine::error("Connection to container lost; closing browser connection {$browserConnection->resourceId}");

            $browserConnection->send(
                Message::terminalData("\n\rLost connection to Tinker container.")
            );

            $browserConnection->close();
        });
    }
}
