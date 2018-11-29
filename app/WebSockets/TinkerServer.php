<?php

namespace App\WebSockets;

use App\Exceptions\ConnectionNotFoundException;
use Exception;
use Illuminate\Support\Collection;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Wilderborn\Partyline\Facade as Partyline;

class TinkerServer implements MessageComponentInterface
{
    /** @var MessageDispatcher */
    protected $messageDispatcher;

    /** @var Collection */
    protected $connections;

    public function __construct(MessageDispatcher $messageDispatcher)
    {
        $this->messageDispatcher = $messageDispatcher;

        $this->connections = collect();
    }

    public function onOpen(ConnectionInterface $connection)
    {
        Partyline::comment("Client connected");

        $this->connections->push(
            new Connection($connection)
        );
    }

    public function onMessage(ConnectionInterface $connection, $message)
    {
        $connection = $this->findConnection($connection);

        $message = Message::fromJson($message, $connection);

        Partyline::comment("Client messaged: {$message->getPayload()}");

        $this->messageDispatcher->dispatch($message);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $connection = $this->findConnection($connection);

        $connection->onClose();

        $this->connections = $this->connections->reject($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        PartyLine::error("An error has occurred: {$exception->getMessage()}");

        $connection->close();
    }

    protected function findConnection(ConnectionInterface $browserConnection): Connection
    {
        $connection = $this->connections->first->usesBrowserConnection($browserConnection);

        return throw_unless($connection, ConnectionNotFoundException::class, $browserConnection);
    }
}
