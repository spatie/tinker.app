<?php

namespace App\WebSockets\Controllers;

use \Ratchet\MessageComponentInterface;
use App\Services\Docker\TinkerContainer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

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
            $this->tinkerContainer->onMessage($this->loop, function ($message) use ($conn) {
                $conn->send((string) $message);
            });
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->tinkerContainer->sendToWebSocket($msg);

        echo sprintf('Connection %d sending message "%s" to other connection' . "\n", $from->resourceId, $msg);
    }
}
