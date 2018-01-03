<?php

namespace App\Services\Docker;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use React\EventLoop\LoopInterface;

class ContainerManager
{
    /** @var \Illuminate\Support\Collection */
    protected $tinkerContainers;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function find(string $name): ?TinkerContainer
    {
        $docker = Docker::create();

        $containers = collect($docker->containerList());

        $container = $containers->first(function (ContainerSummaryItem $container) use ($name) {
            return array_has($container->getNames(), $name);
        });

        if (! $container) {
            return null;
        }

        return new TinkerContainer($this->loop, $name);
    }

    public function findBySessionId(string $sessionId): ?TinkerContainer
    {
        return $this->find("tinker-{$sessionId}");
    }
}
