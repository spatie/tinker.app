<?php

namespace App\Docker;

use ArrayObject;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\HostConfig;
use Docker\API\Model\HostConfigPortBindingsItem;
use Docker\Docker;
use Exception;
use Http\Client\Socket\Exception\ConnectionException;

class ContainerFactory
{
    /** @var Docker */
    protected $docker;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function create(): Container
    {
        $name = str_random();

        $hostPortBinding = (new HostConfigPortBindingsItem())
            ->setHostIp('0.0.0.0'); // if we don't specify a host port Docker will assign one

        $mapPorts = new ArrayObject();
        $mapPorts['22/tcp'] = [$hostPortBinding];

        $hostConfig = (new HostConfig())
            ->setPortBindings($mapPorts);

        $containerProperties = (new ContainersCreatePostBody())
            ->setImage('spatie/tinker.app-image')
            ->setHostConfig($hostConfig)
            ->setTty(true)
            ->setOpenStdin(true)
            ->setAttachStdin(true)
            ->setAttachStdout(true)
            ->setAttachStderr(true);

        try {
            $this->docker->containerCreate($containerProperties, compact('name'));
        } catch (ConnectionException $exception) {
            throw new Exception("Couldn't connect to Docker socket. Is the Docker service running?");
        }

        $container = new Container($name, $this->docker);

        $container->start();

        return $container;
    }
}
