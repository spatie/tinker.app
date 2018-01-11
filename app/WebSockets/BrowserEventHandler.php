<?php

namespace App\WebSockets;

use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;

class BrowserEventHandler
{
    public function onOpen(ConnectionInterface $browserConnection, ContainerConnection $containerConnection)
    {
        $browserConnection->send(Message::terminalData("Loading session...\n\r"));

        $sessionId = $this->getQueryParameter($browserConnection->httpRequest, 'sessionId');

        $containerConnection->startSession($sessionId);
    }

    /**
     * @param ConnectionInterface $browserConnection
     * @param string $message
     */
    public function onMessage(ConnectionInterface $browserConnection, ContainerConnection $containerConnection, Message $message)
    {
        if ($message->getType() === Message::TERMINAL_DATA_TYPE) {
            $containerConnection->sendMessage($message->getPayload());
        }
    }

    public function onClose(ConnectionInterface $browserConnection, ContainerConnection $containerConnection)
    {
    }

    protected function getQueryParameter(Request $request, string $key): ?string
    {
        parse_str($request->getUri()->getQuery(), $queryParams);

        return $queryParams[$key] ?: null;
    }
}
