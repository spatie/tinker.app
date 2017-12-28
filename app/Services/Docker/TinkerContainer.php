<?php

namespace App\Services\Docker;

use App\Services\WebSocketConnection;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\Docker;
use Docker\Stream\AttachWebsocketStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Ratchet\Client\WebSocket;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory;
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

    public function start()
    {
        $this->docker->containerStart($this->name);
    }

    public function sendToWebSocket($message)
    {
        $this->webSocket->send($message);
    }

    public function attachWebSocketStream(ConnectionInterface $clientConnection, LoopInterface $loop)
    {
        $response = $this->docker->containerAttachWebsocket($this->name, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ], false);

        $stream = $response->getBody()->detach();

        // $loop = Factory::create();

        $connection = new WebSocketConnection($stream, $loop);

        $this->webSocket = new WebSocket($connection, new Response, new Request('GET', '/ws'));

        $this->webSocket->on('message', function ($msg) use ($clientConnection) {
            // echo $msg;
            $clientConnection->send($msg);
        });

        $loop->run();
    }
}
