<?php

namespace App\WebSockets\Handlers;

use App\Docker\Container;
use App\Docker\ContainerFactory;
use App\Docker\ContainerRepository;
use App\WebSockets\Connection;
use App\WebSockets\Message;
use Wilderborn\Partyline\Facade as Partyline;

class SessionStartHandler
{
    /** @var ContainerRepository */
    protected $containerRepository;

    /** @var ContainerFactory */
    protected $containerFactory;

    public function __construct(ContainerRepository $containerRepository, ContainerFactory $containerFactory)
    {
        $this->containerRepository = $containerRepository;

        $this->containerFactory = $containerFactory;
    }

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
        $container = $this->containerFactory->create();

        $this->bindContainer($connection, $container);
    }

    protected function loadSession(Connection $connection, string $sessionId)
    {
        $container = $this->containerRepository->find($sessionId);

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
