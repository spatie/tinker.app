<?php

namespace App\WebSockets;

use App\Docker\Container;
use Exception;
use Illuminate\Support\Collection;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use Wilderborn\Partyline\Facade as Partyline;

class TinkerServer implements MessageComponentInterface
{
    /** @var LoopInterface */
    protected $loop;

    /** @var MessageHandler */
    protected $messageHandler;

    /** @var Collection */
    protected $connections;

    public function __construct(LoopInterface $loop, MessageHandler $messageHandler)
    {
        $this->loop = $loop;
        $this->messageHandler = $messageHandler;

        $this->connections = collect();
    }

    public function onOpen(ConnectionInterface $connection)
    {
        Partyline::comment("Client connected");

        $this->connections->push(
            new Connection($connection, $this->loop)
        );
    }

    public function onMessage(ConnectionInterface $connection, $message)
    {
        $connection = $this->findConnection($connection);

        $message = Message::fromJson($message, $connection);

        $this->messageHandler->handle($message);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $connection = $this->findConnection($connection);

        if ($container = $connection->getContainer()) {
            $container->stop();
        }

        $this->connections = $this->connections->reject($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        PartyLine::error("An error has occurred: {$exception->getMessage()}");

        $connection->close();
    }

    protected function findConnection(ConnectionInterface $connection): ?Connection
    {
        return $this->connections->first->usesBrowserConnection($connection);
    }
}
