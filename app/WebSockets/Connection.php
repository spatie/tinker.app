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

    public function send(Message $message)
    {
        $this->browserConnection->send($message);
    }

    public function close()
    {
        $this->browserConnection->close();
    }

    public function setCode(string $code): self
    {
        $this->getContainer()->getContainerModel()->update(['code' => $code]);

        $this->getContainer()->sendFileContents('tinker_buffer', $code);

        return $this;
    }
}
