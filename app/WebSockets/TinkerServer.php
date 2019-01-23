<?php

namespace App\WebSockets;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Wilderborn\Partyline\Facade as Partyline;

class TinkerServer implements MessageComponentInterface
{
    /** @var MessageDispatcher */
    protected $messageDispatcher;

    /** @var WebSocketConnectionRepository */
    protected $connectionRepository;

    public function __construct(MessageDispatcher $messageDispatcher, WebSocketConnectionRepository $connectionRepository)
    {
        $this->messageDispatcher = $messageDispatcher;

        $this->connectionRepository = $connectionRepository;
    }

    public function onOpen(ConnectionInterface $browserConnection)
    {
        Partyline::comment("Client connected");

        $this->connectionRepository->push(
            new Connection($browserConnection)
        );
    }

    public function onMessage(ConnectionInterface $connection, $message)
    {
        $connection = $this->connectionRepository->findForBrowserConnection($connection);

        $message = Message::fromJson($message, $connection);

        Partyline::comment("Client messaged: {$message->getPayload()}");

        $this->messageDispatcher->dispatch($message);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $connection = $this->connectionRepository->findForBrowserConnection($connection);

        $connection->onClose();

        $this->connectionRepository->remove($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        PartyLine::error("An error has occurred: {$exception->getMessage()}");

        throw $exception;

        report($exception);

        $connection->close();
    }
}
