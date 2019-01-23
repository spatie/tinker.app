<?php

namespace App\Docker;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;

class ContainerRepository
{
    /** @var Docker */
    protected $docker;

    /** @var array */
    protected $containers = [];

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function find(string $name): ?Container
    {
        if (array_has($this->containers, $name)) {
            return $this->containers[$name];
        }

        $container = collect($this->docker->containerList())
            ->first(function (ContainerSummaryItem $container) use ($name) {
                return in_array('/' . $name, $container->getNames());
            });

        if (! $container) {
            return null;
        }

        return new Container($name, $this->docker);
    }

    public function push(Container $container): self
    {
        if (array_has($this->containers, $container->getName())) {
            return $this;
        }

        $this->containers[$container->getName()] = $container;

        return $this;
    }
}
