<?php

namespace App\WebSockets;

use Exception;
use PartyLine;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;

class WebSocketEventHandler implements MessageComponentInterface
{
    /** @var \App\WebSockets\BrowserEventHandler  */
    protected $browserHandler;

    /** @var \React\EventLoop\LoopInterface  */
    protected $loop;

    public function __construct(LoopInterface $loop, BrowserEventHandler $browserHandler)
    {
        $this->loop = $loop;
        $this->browserHandler = $browserHandler;
    }

    public function onOpen(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("New connection! ({$browserConnection->resourceId})");

        $this->browserHandler->onOpen($browserConnection);
    }

    /**
     * @param ConnectionInterface $browserConnection
     * @param string $message
     */
    public function onMessage(ConnectionInterface $browserConnection, $message)
    {
        $message = Message::fromJson($message);

        PartyLine::comment("Connection {$browserConnection->resourceId} sending message `{$message->getPayload()}` ({$message->getType()})");

        $this->browserHandler->onMessage($browserConnection, $message);
    }

    public function onClose(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("Connection {$browserConnection->resourceId} has disconnected");

        $this->browserHandler->onClose($browserConnection);
    }

    public function onError(ConnectionInterface $browserConnection, Exception $exception)
    {
        PartyLine::error("An error has occurred: {$exception->getMessage()}");

        dump($exception);

        $browserConnection->close();
    }
}
