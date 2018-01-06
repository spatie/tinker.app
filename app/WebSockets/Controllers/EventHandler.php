<?php

namespace App\WebSockets\Controllers;

use \App\WebSockets\Client;
use \Ratchet\MessageComponentInterface;
use App\Services\Docker\Container;
use App\Services\Docker\ContainerRepository;
use Exception;
use GuzzleHttp\Psr7\Request;
use PartyLine;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use SplObjectStorage;

class EventHandler implements MessageComponentInterface
{
    /** @var \App\Services\Docker\ContainerRepository */
    protected $containerManager;

    /** @var \SplObjectStorage */
    protected $clients;

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->clients = new SplObjectStorage();

        $this->containerManager = new ContainerRepository($loop);
    }

    public function onOpen(ConnectionInterface $connection)
    {
        PartyLine::comment("New connection! ({$connection->resourceId})");

        $connection->send("Loading Tinker session...\n\r");

        $client = new Client($connection, $this->loop);

        $sessionId = $this->getQueryParam($connection->httpRequest, 'sessionId');

        $container = $this->getContainer($sessionId);

        if (!$container) {
            return;
        }

        $client->attachContainer($container);

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

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        PartyLine::error("An error has occurred: {$exception->getMessage()}");

        $connection->close();
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        $client = $this->getClientForConnection($from);

        $client->sendToTinker($message);

        PartyLine::comment("Connection {$from->resourceId} sending message `{$message}` to other connection");
    }

    protected function getContainer(string $sessionId, ConnectionInterface $connection): ?Container
    {
        if ($sessionId) {
            $container = $this->containerManager->findBySessionId($sessionId);

            if (!$container) {
                $connection->send("Session id `{$sessionId}` is invalid.\n\r");
                $connection->close();

                return null;
            }

            $connection->send("Session id `{$sessionId}` found.\n\r");

            return $container;
        }

        $container = (Container::create($this->loop))->start();

        $connection->send("New Tinker session created ({$container->getName()})\n\r");

        return $container;
    }

    protected function getClientForConnection(ConnectionInterface $connection): ?Client
    {
        return collect($this->clients)->first(function ($client, $key) use ($connection) {
            return $client->getConnection() === $connection;
        });
    }

    protected function getQueryParam(Request $request, string $key): ?string
    {
        parse_str($request->getUri()->getQuery(), $queryParams);

        return $queryParams[$key] ?: null;
    }
}
