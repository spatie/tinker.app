<?php

namespace App\WebSockets\Handlers;

use App\WebSockets\Connection;
use App\WebSockets\Message;

class UpdateCodeBufferHandler
{
    public function __invoke(Message $message)
    {
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
