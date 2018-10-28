<?php

namespace App\Exceptions;

use Exception;
use Ratchet\ConnectionInterface;

class ConnectionNotFoundException extends Exception
{
    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct("No active connection found.");
    }
}
