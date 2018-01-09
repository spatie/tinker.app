<?php

namespace App\Services\Docker;

use Closure;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\Docker;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;

class Container
{
    /** @var string */
    protected $name;

    /** @var \Docker\Docker */
    protected $docker;

    /** @var \Ratchet\Client\WebSocket */
    protected $webSocket;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public static function create(LoopInterface $loop): self
    {
        $name = 'tinker-'.str_random();

        $containerProperties = (new ContainersCreatePostBody())
            ->setImage('spatie/tinker.sh-image')
            ->setTty(true)
            ->setOpenStdin(true)
            ->setAttachStdin(true)
            ->setAttachStdout(true)
            ->setAttachStderr(true);

        $docker = Docker::create();

        $docker->containerCreate($containerProperties, compact('name'));

        return new static($name, $loop, $docker);
    }

    public function __construct(string $name, LoopInterface $loop, Docker $docker)
    {
        $this->name = $name;

        $this->loop = $loop;

        $this->docker = $docker;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function start(): self
    {
        $this->docker->containerStart($this->name);

        return $this;
    }

    public function stop(): self
    {
        $this->docker->containerStop($this->name);

        return $this;
    }

    public function remove(): self
    {
        $this->docker->containerDelete($this->name);

        return $this;
    }

    public function sendMessage($message)
    {
        $this->attachToWebSocket();

        $this->webSocket->send($message);
    }

    public function onMessage(Closure $callback): self
    {
        $this->attachToWebSocket();

        $this->webSocket->on('message', $callback);

        return $this;
    }

    public function onClose(Closure $callback): self
    {
        $this->attachToWebSocket();

        $this->webSocket->on('close', $callback);

        return $this;
    }

    protected function attachToWebSocket(): self
    {
        if ($this->webSocket) {
            return $this;
        }

        $response = $this->docker->containerAttachWebsocket($this->name, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ], false);

        $stream = $response->getBody()->detach();

        $connection = new WebSocketConnection($stream, $this->loop);

        $this->webSocket = new WebSocket($connection, new Response, new Request('GET', '/ws'));

        return $this;
    }
}
