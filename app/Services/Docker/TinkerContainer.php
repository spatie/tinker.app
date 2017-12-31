<?php

namespace App\Services\Docker;

use App\Services\WebSocketConnection;
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

    public function __construct(?Docker $docker = null)
    {
        $this->docker = $docker ?? Docker::create();

        $this->name = 'tinker-'.str_random();

        $containerCreatePostBody = new ContainersCreatePostBody();
        $containerCreatePostBody->setImage('alexvanderbist/tinker-sh-image');
        $containerCreatePostBody->setTty(true);
        $containerCreatePostBody->setOpenStdin(true); // -i interactive flag = keep stdin open even when not attached
        // $containerCreatePostBody->setStdinOnce(true); // close stdin after client dc
        $containerCreatePostBody->setAttachStdin(true);
        $containerCreatePostBody->setAttachStdout(true);
        $containerCreatePostBody->setAttachStderr(true);

        $this->docker->containerCreate($containerCreatePostBody, ['name' => $this->name]);
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
        $this->webSocket->send($message);
    }

    public function onMessage(LoopInterface $loop, \Closure $callback)
    {
        $response = $this->docker->containerAttachWebsocket($this->name, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ], false);

        $stream = $response->getBody()->detach();

        $connection = new WebSocketConnection($stream, $loop);

        $this->webSocket = new WebSocket($connection, new Response, new Request('GET', '/ws'));

        $this->webSocket->on('message', $callback);
    }
}
