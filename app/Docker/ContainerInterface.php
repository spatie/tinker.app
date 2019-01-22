<?php

namespace App\Docker;

use Illuminate\Support\Collection;

interface ContainerInterface
{
    public function getConnections(): Collection;

    public function writeData($data);

    public function stop();
}
