<?php

namespace App\WebSockets;

use Exception;
use GuzzleHttp\Psr7\Request;
use PartyLine;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use SplObjectStorage;

class BrowserEventHandler implements MessageComponentInterface
{
    /** @var \SplObjectStorage */
    protected $containerConnections;

    /** @var \React\EventLoop\LoopInterface  */
    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->containerConnections = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("New connection! ({$browserConnection->resourceId})");

        $browserConnection->send(
            Message::terminalData("Loading session...\n\r")
        );

        $sessionId = $this->getQueryParameter($browserConnection->httpRequest, 'sessionId');

        $containerConnection = new ContainerConnection($browserConnection, $this->loop, $sessionId);

        $this->containerConnections->attach($containerConnection);
    }

    /**
     * @param ConnectionInterface $browserConnection
     * @param string $message
     */
    public function onMessage(ConnectionInterface $browserConnection, $message)
    {
        $message = Message::terminalData($message);

        $this->findContainerConnection($browserConnection)->sendMessage($message->getPayload());

        PartyLine::comment("Connection {$browserConnection->resourceId} sending message `{$message->getPayload()}` ({$message->getType()}) to other connection");
    }

    public function onClose(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("Connection {$browserConnection->resourceId} has disconnected");

        if ($containerConnection = $this->findContainerConnection($browserConnection)) {
            $containerConnection->close();
            $this->containerConnections->detach($containerConnection);
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

    protected function getQueryParameter(Request $request, string $key): ?string
    {
        parse_str($request->getUri()->getQuery(), $queryParams);

        return $queryParams[$key] ?: null;
    }
}
