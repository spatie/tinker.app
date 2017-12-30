<?php

namespace App\WebSockets\Controllers;

use App\Services\Client;
use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use \Ratchet\MessageComponentInterface;

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
        echo $this->clients;
        var_dump($this->clients);
        //$client = $this->getClientForConnection($from);
        // echo $client;
        // var_dump($client);
        //$client->tinkerContainer->sendToWebSocket($msg);

        //echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
    }

    protected function getClientForConnection(ConnectionInterface $conn): ?Client
    {
        return collect($this->clients)->first(function ($client, $key) use ($conn) {
            return $client->connection == $conn;
        });
    }
}
