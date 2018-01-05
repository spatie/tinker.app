<?php

namespace App\WebSockets\Controllers;

use \App\WebSockets\Client;
use \Ratchet\MessageComponentInterface;
use App\Services\Docker\ContainerManager;
use App\Services\Docker\TinkerContainer;
use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use PartyLine;
use SplObjectStorage;

class EventHandler implements MessageComponentInterface
{
    /** @var \App\Services\Docker\ContainerManager */
    protected $containerManager;

    /** @var \SplObjectStorage */
    protected $clients;

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->clients = new SplObjectStorage();

        $this->containerManager = new ContainerManager($loop);
    }

    public function onOpen(ConnectionInterface $connection)
    {
        PartyLine::comment("New connection! ({$connection->resourceId})");

        $connection->send("Loading Tinker session...\n\r");

        $sessionId = $this->getQueryParam($connection->httpRequest, 'sessionId');

        $client = new Client($connection, $this->loop);

        $tinkerContainer = $this->getTinkerContainer();

        if (!$tinkerContainer) {
            return;
        }

        $client->attachContainer($tinkerContainer);

        $this->clients->attach($client);
    }

    public function onClose(ConnectionInterface $connection)
    {
        PartyLine::comment("Connection {$connection->resourceId} has disconnected");

        $client = $this->getClientForConnection($connection);

        if ($client) {
            $client->cleanupContainer();
            $this->clients->detach($client);
        }
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        PartyLine::error("An error has occurred: {$e->getMessage()}");

        $connection->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $client = $this->getClientForConnection($from);

        $client->sendToTinker($msg);

        PartyLine::comment("Connection {$from->resourceId} sending message `{$msg}` to other connection");
    }

    protected function getTinkerContainer(string $sessionId, ConnectionInterface $connection): ?TinkerContainer
    {
        if ($sessionId) {

            $tinkerContainer = $this->containerManager->findBySessionId($sessionId);

            if (!$tinkerContainer) {

                $connection->send("Session id `{$sessionId}` is invalid.\n\r");
                $connection->close();

                return null;
            }

            $connection->send("Session id `{$sessionId}` found.\n\r");

            return $tinkerContainer;
        }

        $tinkerContainer = (TinkerContainer::create($this->loop))->start();

        $connection->send("New Tinker session created ({$tinkerContainer->getName()})\n\r");

        return $tinkerContainer;
    }

    protected function getClientForConnection(ConnectionInterface $connection): ?Client
    {
        return collect($this->clients)->first(function ($client, $key) use ($connection) {
            return $client->getConnection() == $connection;
        });
    }

    protected function getQueryParam(Request $request, string $key): ?string
    {
        parse_str($request->getUri()->getQuery(), $queryParams);

        return $queryParams[$key] ?: null;
    }
}
