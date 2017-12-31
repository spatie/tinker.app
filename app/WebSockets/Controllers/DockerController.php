<?php

namespace App\WebSockets\Controllers;

use \Ratchet\MessageComponentInterface;
use App\Services\Client;
use Ratchet\ConnectionInterface;
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

    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";

        $client = new Client($conn, $this->loop);

        $this->clients->attach($client);
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "Connection {$conn->resourceId} has disconnected\n";

        $client = $this->getClientForConnection($conn);

        $this->clients->detach($client);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $client = $this->getClientForConnection($from);

        $client->sendToTinker($msg);

        echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
    }

    protected function getClientForConnection(ConnectionInterface $conn): ?Client
    {
        foreach ($this->clients as $client) {
            if ($client->getConnection() == $conn) {
                return $client;
            }
        }

        return null; // throw exception or something?
    }
}
