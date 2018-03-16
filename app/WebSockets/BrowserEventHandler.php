<?php

namespace App\WebSockets;

use App\Services\Docker\Container;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use Partyline;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class BrowserEventHandler
{
    /** @var \Illuminate\Support\Collection */
    protected $containerConnections;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->containerConnections = new Collection();
    }

    public function onOpen(ConnectionInterface $browserConnection)
    {
        $browserConnection->send(Message::terminalData("Loading session...\n\r"));

        $sessionId = $this->getQueryParameter($browserConnection->httpRequest, 'sessionId');

        $containerConnection = new ContainerConnection($browserConnection, $this->loop, $sessionId);

        $this->containerConnections->push($containerConnection);
    }

    public function onMessage(ConnectionInterface $browserConnection, Message $message)
    {
        $containerConnection = $this->findContainerConnection($browserConnection);

        if ($message->getType() === Message::TERMINAL_DATA_TYPE) {
            $containerConnection->sendMessage($message->getPayload());
        }

        if ($message->getType() === Message::BUFFER_RUN_TYPE) {
            $containerConnection->sendFileContents('tinker_buffer', $message->getPayload());
        }

        if ($message->getType() === Message::BUFFER_CHANGE_TYPE) {
            $container = $containerConnection->getContainer();

            $collaboratingContainerConnections = $this->findConnectionsUsingContainer($container);

            $collaboratingBrowserConnections = $collaboratingContainerConnections->map->getBrowserConnection();

            $bufferChangeMessage = Message::bufferChange($message->getPayload());

            $collaboratingBrowserConnections
                ->reject(function (ConnectionInterface $collaboratingBrowserConnection) use ($browserConnection) {
                    return $collaboratingBrowserConnection === $browserConnection;
                })
                ->each->send($bufferChangeMessage);
        }
    }

    public function onClose(ConnectionInterface $browserConnection)
    {
        $containerConnection = $this->findContainerConnection($browserConnection);
        $container = $containerConnection->getContainer();

        if (is_null($container)) {
            return;
        }

        if ($this->findConnectionsUsingContainer($container)->count() === 1) {
            Partyline::comment("Last client on {$container->getName()} disconnected. Shutting down container.");

            $container->kill()->remove();
        }

        $this->containerConnections = $this->containerConnections->reject->usesBrowserConnection($browserConnection);
    }

    protected function findConnectionsUsingContainer(Container $container): Collection
    {
        return $this
            ->containerConnections
            ->filter(function (ContainerConnection $containerConnection) use ($container) {
                return $container->getName() === optional($containerConnection->getContainer())->getName();
            });
    }

    protected function findContainerConnection(ConnectionInterface $browserConnection): ?ContainerConnection
    {
        return $this->containerConnections->first->usesBrowserConnection($browserConnection);
    }

    protected function getQueryParameter(Request $request, string $key): ?string
    {
        parse_str($request->getUri()->getQuery(), $queryParams);

        return $queryParams[$key] ?: null;
    }
}
