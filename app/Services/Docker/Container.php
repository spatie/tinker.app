<?php

namespace App\Services\Docker;

use Closure;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\HostConfig;
use Docker\API\Model\HostConfigPortBindingsItem;
use Docker\Docker;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Partyline;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use stdClass;

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

    /** @var int */
    protected $sshPort;

    /** @var \League\Flysystem\Filesystem */
    protected $filesystem;

    public static function create(LoopInterface $loop): self
    {
        $name = 'tinker-'.str_random();

        $hostPortBinding = (new HostConfigPortBindingsItem())
            ->setHostIp('0.0.0.0'); // if we don't specify a host port Docker will assign one

        $mapPorts = new \ArrayObject();
        $mapPorts['22/tcp'] = [$hostPortBinding];

        $hostConfig = (new HostConfig())
            ->setPortBindings($mapPorts);

        $containerProperties = (new ContainersCreatePostBody())
            ->setImage('spatie/tinker.sh-image')
            ->setHostConfig($hostConfig)
            ->setTty(true)
            ->setOpenStdin(true)
            ->setAttachStdin(true)
            ->setAttachStdout(true)
            ->setAttachStderr(true);

        $docker = Docker::create();

        $docker->containerCreate($containerProperties, compact('name'));

        return new static($name, $loop, $docker);
    }

    public function __construct(string $name, LoopInterface $loop, ?Docker $docker = null)
    {
        $this->name = $name;

        $this->loop = $loop;

        $this->docker = $docker ?? Docker::create();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInfo(): stdClass
    {
        $response = $this->docker->containerInspect($this->getName(), [], false);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Couldnt get info for docker container.');
        }

        $json = (string) $response->getBody();

        return json_decode($json);
    }

    public function getSshPort(): string
    {
        if ($this->sshPort) {
            return $this->sshPort;
        }

        $this->sshPort = $this->getInfo()->NetworkSettings->Ports->{'22/tcp'}[0]->HostPort;

        return $this->sshPort;
    }

    public function getFilesystem(): Filesystem
    {
        if ($this->filesystem) {
            return $this->filesystem;
        }

        $adapter = new SftpAdapter([
            'host' => 'localhost',
            'port' => $this->getSshPort(),
            'username' => 'root',
            'password' => 'tinkersh',
            'root' => '/var/www',
            'timeout' => 10,
            'directoryPerm' => 0755
        ]);

        $this->filesystem = new Filesystem($adapter);

        return $this->filesystem;
    }

    public function start(): self
    {
        $this->docker->containerStart($this->name);

        Partyline::info("SSH available locally on port {$this->getSshPort()}");

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
