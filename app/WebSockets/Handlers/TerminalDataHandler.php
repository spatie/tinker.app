<?php

namespace App\WebSockets\Handlers;

use App\WebSockets\Message;

class TerminalDataHandler
{
    public function __invoke(Message $message)
    {
        $message->from()->getContainer()->sendMessage($message->getPayload());
    }
}
