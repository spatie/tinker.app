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

    public function startSession()
    {
        $this->container = $this->findOrCreateContainer();

        $this->bindContainer();
    }

    protected function findOrCreateContainer(?string $sessionId = null): ?Container
    {
        if ($sessionId) {
            return $this->findContainer($sessionId);
        }

        $container = (Container::create($this->loop))->start();

        $this->browserConnection->send(
            Message::terminalData("New container created ({$container->getName()})\n\r")
        );

        return $container;
    }

    protected function findContainer(string $sessionId): ?Container
    {
        $container = (new ContainerRepository($this->loop))->findBySessionId($sessionId);

        if (! $container) {
            $this->browserConnection->send(
                Message::terminalData("Session id `{$sessionId}` is invalid.\n\r")
            );

            $this->browserConnection->close();

            return null;
        }

        $this->browserConnection->send(
            Message::terminalData("Session id `{$sessionId}` found.\n\r")
        );

        return $container;
    }

    protected function bindContainer()
    {
        $this->container->onMessage(function ($message) {
            $this->browserConnection->send(Message::terminalData((string) $message));
        });

        $this->container->onClose(function () {
            PartyLine::error("Connection to container lost; closing browser connection {$this->browserConnection->resourceId}");

            $this->browserConnection->send(
                Message::terminalData("\n\rLost connection to Tinker container.")
            );

            $this->browserConnection->close();
        });
    }
}
