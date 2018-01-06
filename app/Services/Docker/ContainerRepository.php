<?php

namespace App\Services\Docker;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use React\EventLoop\LoopInterface;

class ContainerRepository
{
    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function find(string $name): ?Container
    {
        $docker = Docker::create();

        $container = collect($docker->containerList())->first(function (ContainerSummaryItem $container) use ($name) {
            return in_array('/' . $name, $container->getNames());
        });

        if (!$container) {
            return null;
        }

        return new Container($this->loop, $name);
    }

    public function findBySessionId(string $sessionId): ?Container
    {
        return $this->find("tinker-{$sessionId}");
    }
}
