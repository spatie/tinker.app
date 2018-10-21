<?php

namespace App\Docker;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use Illuminate\Support\Collection;
use React\EventLoop\LoopInterface;

class ContainerRepository
{
    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    /** @var Collection */
    protected $containers;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->containers = collect();
    }

    public function find(string $name): ?Container
    {
        // Check containers array

        // Check docker containers

        // Check database and initialise new

        $container = collect(Docker::create()->containerList())
            ->first(function (ContainerSummaryItem $container) use ($name) {
                return in_array('/' . $name, $container->getNames());
            });

        if (! $container) {
            return null;
        }

        return new Container($name, $this->loop);
    }

    public function findBySessionId(string $sessionId): ?Container
    {
        return $this->find($sessionId);
    }

    public function push(Container $container)
    {
        $this->containers->push($container);
    }
}
