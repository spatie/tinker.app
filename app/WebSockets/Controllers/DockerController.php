<?php

namespace App\WebSockets\Controllers;

use \Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class DockerController implements MessageComponentInterface
{
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
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
    }
}
