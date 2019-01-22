<?php

namespace App\Docker;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use React\EventLoop\LoopInterface;

class ContainerRepository
{
    /** @var Docker */
    protected $docker;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function find(string $name): ?Container
    {
        $container = collect($this->docker->containerList())
            ->first(function (ContainerSummaryItem $container) use ($name) {
                return in_array('/' . $name, $container->getNames());
            });

        if (! $container) {
            return null;
        }

        return new Container($name, $this->docker);
    }
}
