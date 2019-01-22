<?php

namespace App\Docker;

use Illuminate\Support\Collection;

class NullContainer implements ContainerInterface
{
    public function getConnections(): Collection
    {
        return collect();
    }

    public function writeData($data)
    {
    }

    public function stop()
    {
    }
}
