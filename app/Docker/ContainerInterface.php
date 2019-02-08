<?php

namespace App\Docker;

interface ContainerInterface
{
    public function writeData($data);

    public function stop();

    public function getName(): string;
}
