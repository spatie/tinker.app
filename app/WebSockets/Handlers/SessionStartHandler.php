<?php

namespace App\WebSockets\Handlers;

use App\Docker\Container;
use App\WebSockets\Connection;
use App\WebSockets\Message;
use Wilderborn\Partyline\Facade as Partyline;

class SessionStartHandler
{
    public function __invoke(Message $message)
    {
        $container = Container::findOrCreate();

        $connection = $message->from();

        if (! $container) {
            $connection->send(
                Message::terminalData("Session id `SESSION_ID` is invalid.\n\r")
            );

            $connection->close();

            return;
        }

        $connection->send(
            Message::terminalData("Session id `{SESSION_ID}` found.\n\r")
        );

        $this->bindContainer($connection, $container);
    }

    protected function bindContainer(Connection $connection, Container $container)
    {
        $connection->setContainer($container);

        $container->onMessage(function ($message) use ($connection) {
            $connection->send(Message::terminalData((string) $message));
        });

        $container->onClose(function () use ($connection) {
            PartyLine::error("Connection to container lost; closing browser connection {$connection->getBrowserConnection()->resourceId}");

            $connection->send(
                Message::terminalData("\n\rLost connection to Tinker container.")
            );

            $connection->close();
        });
    }
}
