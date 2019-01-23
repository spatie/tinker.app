<?php

namespace App\WebSockets\Handlers;

use App\WebSockets\Connection;
use App\WebSockets\Message;
use App\WebSockets\WebSocketConnectionRepository;

class UpdateCodeBufferHandler
{
    /** @var WebSocketConnectionRepository */
    protected $connectionRepository;

    public function __construct(WebSocketConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function __invoke(Message $message)
    {
        $container = $message->from()->getContainer();

        $collaboratingConnections = $this->connectionRepository->getConnectedToContainer($container);

        $bufferChangeMessage = Message::bufferChange($message->getPayload());

        collect($collaboratingConnections)
            ->reject(function (Connection $collaboratingConnection) use ($message) {
                return $collaboratingConnection === $message->from();
            })
            ->each->send($bufferChangeMessage);
    }
}
