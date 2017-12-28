<?php

namespace App\WebSockets\Controllers;

use \Ratchet\MessageComponentInterface;
use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;

class DockerController implements MessageComponentInterface
{
    /** @var \SplObjectStorage  */
    protected $clients;

    protected $tinkerContainer;

    public function onClose(ConnectionInterface $conn)
    {
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";

        // Create docker container if we don't have one yet
        if (! $this->tinkerContainer) {
            $this->tinkerContainer = new TinkerContainer();
            $this->tinkerContainer->start();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
    }
}
