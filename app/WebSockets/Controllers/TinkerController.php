<?php

namespace App\WebSockets\Controllers;

use \App\WebSockets\Client;
use \Ratchet\MessageComponentInterface;
use App\Services\Docker\ContainerManager;
use App\Services\Docker\TinkerContainer;
use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class TinkerController implements MessageComponentInterface
{
    /** @var \App\Services\Docker\ContainerManager */
    protected $containerManager;

    /** @var \SplObjectStorage  */
    protected $clients;

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->clients = new \SplObjectStorage();

        $this->containerManager = new ContainerManager($loop);
    }

    public function onOpen(ConnectionInterface $connection)
    {
        echo "New connection! ({$connection->resourceId})\n";

        $connection->send("Loading Tinker session...\n\r");

        $sessionId = $this->getQueryParam($connection->httpRequest, 'sessionId');

        if ($sessionId) {
            $tinkerContainer = $this->containerManager->findBySessionId($sessionId);

            if (! $tinkerContainer) {
                $connection->send("Session id `{$sessionId}` is invalid.\n\r");
                $connection->close();
            }
        } else {
            $tinkerContainer = TinkerContainer::create($this->loop);
            $tinkerContainer->start();
        }

        $client = new Client($connection, $this->loop);

        $client->attachContainer($tinkerContainer);

        $this->clients->attach($client);
    }

    public function onClose(ConnectionInterface $connection)
    {
        echo "Connection {$connection->resourceId} has disconnected\n";

        $client = $this->getClientForConnection($connection);

        $client->cleanupContainer();

        $this->clients->detach($client);
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $connection->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $client = $this->getClientForConnection($from);

        $client->sendToTinker($msg);

        echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
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
