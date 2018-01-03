<?php

namespace App\WebSockets\Controllers;

use \App\WebSockets\Client;
use \Ratchet\WebSocket\MessageComponentInterface;
use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\LoopInterface;

class DockerController implements MessageComponentInterface
{
    /** @var \SplObjectStorage  */
    protected $clients;

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $sessionId = $this->getQueryParam($connection->httpRequest, 'sessionId');

        dump($sessionId);

        echo "New connection! ({$connection->resourceId})\n";

        $client = new Client($connection, $this->loop);

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

    public function onMessage(ConnectionInterface $from, MessageInterface $msg)
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
