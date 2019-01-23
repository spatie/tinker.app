<?php

namespace App\WebSockets;

use App\Docker\ContainerInterface;
use App\Exceptions\ConnectionNotFoundException;
use Ratchet\ConnectionInterface;

class WebSocketConnectionRepository
{
    /** @var array */
    protected $connections = [];

    public function findForBrowserConnection(ConnectionInterface $browserConnection): Connection
    {
        $connection = array_first($this->connections, function (Connection $connection) use ($browserConnection) {
            return $connection->usesBrowserConnection($browserConnection);
        });

        return throw_unless($connection, ConnectionNotFoundException::class, $browserConnection);
    }

    public function getConnectedToContainer(ContainerInterface $container): array
    {
        return array_filter($this->connections, function (Connection $connection) use ($container) {
            return $connection->getContainer() === $container;
        });
    }

    public function push(Connection $connection): self
    {
        $this->connections[] = $connection;

        return $this;
    }

    public function remove(Connection $connectionToRemove): self
    {
        $this->connections = array_filter($this->connections, function (Connection $connection) use ($connectionToRemove) {
            return $connectionToRemove !== $connection;
        });

        return $this;
    }
}
