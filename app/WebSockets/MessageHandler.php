<?php

namespace App\WebSockets;

use App\WebSockets\Handlers\StartSession;

class MessageHandler
{
    public function handle(Message $message)
    {
        if ($message->getType() === Message::SESSION_START_TYPE) {
            $handler = new StartSession($message->from());
            $handler($message);
        }

        if ($message->getType() === Message::TERMINAL_DATA_TYPE) {
            $message->from()->getContainer()->sendMessage($message->getPayload());
        }

        if ($message->getType() === Message::BUFFER_RUN_TYPE) {
            $message->from()->setCode($message->getPayload());
        }

        if ($message->getType() === Message::BUFFER_CHANGE_TYPE) {
            $container = $message->from()->getContainer();

            $collaboratingConnections = $container->getConnections();

            $bufferChangeMessage = Message::bufferChange($message->getPayload());

            $collaboratingConnections
                ->reject(function (Connection $collaboratingConnection) use ($message) {
                    return $collaboratingConnection === $message->from();
                })
                ->each->send($bufferChangeMessage);
        }
    }
}
