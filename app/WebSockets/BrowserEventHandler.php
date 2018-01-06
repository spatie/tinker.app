<?php

namespace App\WebSockets;

use \Ratchet\MessageComponentInterface;
use App\Services\Docker\Container;
use App\Services\Docker\ContainerRepository;
use Exception;
use GuzzleHttp\Psr7\Request;
use PartyLine;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use SplObjectStorage;

class BrowserEventHandler implements MessageComponentInterface
{
    /** @var \SplObjectStorage */
    protected $containerConnections;

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->containerConnections = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("New connection! ({$browserConnection->resourceId})");

        $browserConnection->send("Loading session...\n\r");

        $sessionId = $this->getQueryParameter($browserConnection->httpRequest, 'sessionId');

        $containerConnection = new ContainerConnection($browserConnection, $sessionId, $this->loop);

        $this->containerConnections->attach($containerConnection);
    }

    public function onMessage(ConnectionInterface $browserConnection, $message)
    {
        $containerConnection = $this->findContainerConnection($browserConnection);

        $containerConnection->sendMessage($message);

        PartyLine::comment("Connection {$browserConnection->resourceId} sending message `{$message}` to other connection");
    }

    public function onClose(ConnectionInterface $browserConnection)
    {
        PartyLine::comment("Connection {$browserConnection->resourceId} has disconnected");

        $client = $this->findContainerConnection($browserConnection);

        if ($client) {
            $client->cleanupContainer();
            $this->containerConnections->detach($client);
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
