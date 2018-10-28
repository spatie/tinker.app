<?php

namespace App\WebSockets\Handlers;

use App\WebSockets\Message;

class RunCodeBufferHandler
{
    public function __invoke(Message $message)
    {
        $message->from()->setCode($message->getPayload());
    }
}
