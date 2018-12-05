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
        $sessionId = $message->getPayload();
        $connection = $message->from();

        if (! $sessionId) {
            $this->newSession($connection);

            return;
        }

        $this->loadSession($connection, $sessionId);
    }

    protected function newSession(Connection $connection)
    {
        $container = Container::create();

        $this->bindContainer($connection, $container);
    }

    protected function loadSession(Connection $connection, string $sessionId)
    {
        $container = Container::findBySessionId($sessionId);

        if (! $container) {
            $connection->writeToTerminal("Session id `SESSION_ID` is invalid.\n");

            $connection->close();

            return;
        }

        $connection->writeToTerminal("Session id `{SESSION_ID}` found.\n");

        $this->bindContainer($connection, $container);
    }

    protected function bindContainer(Connection $connection, Container $container)
    {
        $connection->setContainer($container);

        $connection->send(Message::create(Message::SESSION_STARTED_TYPE, json_encode($container->getSessionData())));

        $container->onMessage(function ($message) use ($connection) {
            $connection->writeToTerminal((string) $message);
        });

        $container->onClose(function () use ($connection) {
            PartyLine::error("Connection to container lost; closing browser connection {$connection->getBrowserConnection()->resourceId}");

            $connection->writeToTerminal("\n\rLost connection to Tinker container.");

            $connection->close();
        });
    }
}
