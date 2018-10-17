<?php

namespace App\WebSockets;

use App\Docker\Container;
use App\Docker\ContainerRepository;
use Wilderborn\Partyline\Facade as PartyLine;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class Connection
{
    /** @var ConnectionInterface */
    protected $browserConnection;

    /** @var ?Container */
    protected $container;

    /** @var LoopInterface */
    protected $loop;

    public function __construct(ConnectionInterface $browserConnection, LoopInterface $loop)
    {
        $this->browserConnection = $browserConnection;
        $this->loop = $loop;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function getBrowserConnection(): ConnectionInterface
    {
        return $this->browserConnection;
    }

    public function usesBrowserConnection(ConnectionInterface $browserConnection): bool
    {
        return $this->browserConnection === $browserConnection;
    }

    public function setCode(string $code): self
    {
        $this->getContainer()->getContainerModel()->update(['code' => $code]);

        $this->getContainer()->sendFileContents('tinker_buffer', $code);

        return $this;
    }

    protected function findOrCreateContainer(ConnectionInterface $browserConnection, ?string $sessionId = null): ?Connection
    {
        if ($sessionId) {
            return $this->findContainer($sessionId, $browserConnection);
        }

        $container = (Connection::create($this->loop))->start();

        $browserConnection->send(
            Message::terminalData("New container created ({$container->getName()})\n\r")
        );

        return $container;
    }

    protected function findContainer(string $sessionId, ConnectionInterface $browserConnection): ?Connection
    {
        $container = (new ContainerRepository($this->loop))->findBySessionId($sessionId);

        if (! $container) {
            $browserConnection->send(
                Message::terminalData("Session id `{$sessionId}` is invalid.\n\r")
            );

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
