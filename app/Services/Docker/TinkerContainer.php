<?php

namespace App\Services\Docker;

use Docker\API\Model\ContainersCreatePostBody;
use Docker\Docker;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;

class TinkerContainer
{
    /** @var string */
    protected $name;

    /** @var \Docker\Docker */
    protected $docker;

    /** @var \Ratchet\Client\WebSocket */
    protected $webSocket;

    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    public function __construct(LoopInterface $loop, string $name)
    {
        $this->loop = $loop;

        $this->docker = Docker::create();

        $this->name = $name;
    }

    public static function create(LoopInterface $loop): self
    {
        $name = 'tinker-'.str_random();

        $containerCreatePostBody = new ContainersCreatePostBody();
        $containerCreatePostBody->setImage('spatie/tinker.sh-image');
        $containerCreatePostBody->setTty(true);
        $containerCreatePostBody->setOpenStdin(true); // -i interactive flag = keep stdin open even when not attached
        // $containerCreatePostBody->setStdinOnce(true); // close stdin after client dc
        $containerCreatePostBody->setAttachStdin(true);
        $containerCreatePostBody->setAttachStdout(true);
        $containerCreatePostBody->setAttachStderr(true);

        Docker::create()->containerCreate($containerCreatePostBody, ['name' => $name]);

        return new static($loop, $name);
    }

    protected function attachToWebsocket()
    {
        if ($this->webSocket) {
            return;
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

    public function sendToWebSocket($message)
    {
        $this->attachToWebsocket();

        $this->webSocket->send($message);
    }

    public function onMessage(\Closure $callback): self
    {
        $this->attachToWebsocket();

        $this->webSocket->on('message', $callback);

        return $this;
    }

    public function onClose(\Closure $callback): self
    {
        $this->attachToWebsocket();

        $this->webSocket->on('close', $callback);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
