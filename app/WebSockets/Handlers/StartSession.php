<?php

namespace App\WebSockets\Handlers;

use App\Docker\Container;
use App\WebSockets\Connection;
use App\WebSockets\Message;
use Wilderborn\Partyline\Facade as Partyline;

class StartSession
{
    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(Message $message)
    {
        // $sessionId needed from message somehow?

        $container = Container::findOrCreate();

        if (! $container) {
            $this->connection->send(
                Message::terminalData("Session id `SESSION_ID` is invalid.\n\r")
            );

            $this->connection->close();

            return;
        }

        $this->connection->send(
            Message::terminalData("Session id `{SESSION_ID}` found.\n\r")
        );

        $this->bindContainer($container);
    }

    protected function bindContainer(Container $container)
    {
        $container->onMessage(function ($message) {
            $this->connection->send(Message::terminalData((string) $message));
        });

        $container->onClose(function () {
            PartyLine::error("Connection to container lost; closing browser connection {$this->connection->getBrowserConnection()->resourceId}");

            $this->connection->send(
                Message::terminalData("\n\rLost connection to Tinker container.")
            );

            $this->connection->close();
        });
    }
}
