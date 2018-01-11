<?php

namespace App\WebSockets;

use Exception;
use Illuminate\Support\Collection;
use PartyLine;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;

class WebSocketEventHandler implements MessageComponentInterface
{
    /** @var \Illuminate\Support\Collection */
    protected $containerConnections;

    /** @var \App\WebSockets\BrowserEventHandler  */
    protected $browserHandler;

    /** @var \React\EventLoop\LoopInterface  */
    protected $loop;

    public function __construct(LoopInterface $loop, BrowserEventHandler $browserHandler)
    {
        $this->loop = $loop;
        $this->browserHandler = $browserHandler;

        $this->containerConnections = new Collection();
    }

    public function onOpen(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("New connection! ({$browserConnection->resourceId})");

        $containerConnection = new ContainerConnection($browserConnection, $this->loop);

        $this->containerConnections->push($containerConnection);

        $this->browserHandler->onOpen($browserConnection, $containerConnection);
    }

    /**
     * @param ConnectionInterface $browserConnection
     * @param string $message
     */
    public function onMessage(ConnectionInterface $browserConnection, $message)
    {
        $message = Message::fromJson($message);

        PartyLine::comment("Connection {$browserConnection->resourceId} sending message `{$message->getPayload()}` ({$message->getType()})");

        $containerConnection = $this->findContainerConnection($browserConnection);

        $this->browserHandler->onMessage($browserConnection, $containerConnection, $message);
    }

    public function onClose(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("Connection {$browserConnection->resourceId} has disconnected");

        $containerConnection = $this->findContainerConnection($browserConnection);

        $this->browserHandler->onClose($browserConnection, $containerConnection);

        if ($containerConnection) {
            $containerConnection->close();
            $this->containerConnections->reject->usesBrowserConnection($browserConnection);
        }
    }

    public function onError(ConnectionInterface $browserConnection, Exception $exception)
    {
        PartyLine::error("An error has occurred: {$exception->getMessage()}");

        $browserConnection->close();
    }

    protected function findContainerConnection(ConnectionInterface $browserConnection): ?ContainerConnection
    {
        return collect($this->containerConnections)->first->usesBrowserConnection($browserConnection);
    }
}
