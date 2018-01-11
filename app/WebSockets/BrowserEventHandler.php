<?php

namespace App\WebSockets;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
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
    }

    public function onClose(ConnectionInterface $browserConnection)
    {
        $containerConnection = $this->findContainerConnection($browserConnection);

        if ($containerConnection) {
            $containerConnection->close();
            $this->containerConnections = $this->containerConnections->reject->usesBrowserConnection($browserConnection);
        }
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
