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

    protected $tinkerContainer;

    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";

        $client = new Client($conn, $this->loop);

        $this->tinkerContainer = $client->tinkerContainer;

        $this->clients = $client->clients;
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "Connection {$conn->resourceId} has disconnected\n";

        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->tinkerContainer->sendToWebSocket($msg);

        echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
    }
}
