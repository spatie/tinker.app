<?php

namespace App\Docker;

class NullContainer implements ContainerInterface
{
    public function writeData($data)
    {
    }

    public function stop()
    {
    }
}
