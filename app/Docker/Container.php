<?php

namespace App\Docker;

use App\Models\Container as ContainerModel;
use Closure;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\HostConfig;
use Docker\API\Model\HostConfigPortBindingsItem;
use Docker\Docker;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Wilderborn\Partyline\Facade as Partyline;
use Ratchet\Client\WebSocket;
use stdClass;

class Container implements ContainerInterface
{
    /** @var string */
    protected $name;

    /** @var \Docker\Docker */
    protected $docker;

    /** @var \Ratchet\Client\WebSocket */
    protected $webSocket;

    /** @var int */
    protected $sshPort;

    /** @var \League\Flysystem\Filesystem */
    protected $filesystem;

    /** @var \App\Models\Container */
    protected $containerModel;

    /** @var Collection */
    protected $connections;

    public static function create(): self
    {
        $name = str_random();

        $hostPortBinding = (new HostConfigPortBindingsItem())
            ->setHostIp('0.0.0.0'); // if we don't specify a host port Docker will assign one

        $mapPorts = new \ArrayObject();
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

        $docker = Docker::create();

        $docker->containerCreate($containerProperties, compact('name'));

        $container = new static($name, $docker);

        $container->start();

        return $container;
    }

    public function __construct(string $name, ?Docker $docker = null)
    {
        $this->name = $name;

        $this->docker = $docker ?? Docker::create();

        app(ContainerRepository::class)->push($this);

        $this->connections = collect();

        $this->containerModel = ContainerModel::updateOrCreate([
            'name' => $name,
        ], [
            'active' => true,
        ]);
    }

    public static function findOrCreate(?string $sessionId = null): self
    {
        if ($sessionId) {
            return static::findBySessionId($sessionId);
        }

        return static::create();
    }

    public static function findBySessionId(string $sessionId): ?Container
    {
        return app(ContainerRepository::class)->findBySessionId($sessionId);
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
            'password' => 'tinkerapp',
            'root' => '/var/www',
            'timeout' => 10,
            'directoryPerm' => 0755
        ]);

        $this->filesystem = new Filesystem($adapter);

        return $this->filesystem;
    }

    public function sendFileContents(string $filePath, string $contents): self
    {
        $this->getFilesystem()->put($filePath, $contents);

        $this->writeData("run\n");

        return $this;
    }

    public function getContainerModel(): ContainerModel
    {
        return $this->containerModel;
    }

    public function getConnections(): Collection
    {
        return $this->connections;
    }

    public function start(): self
    {
        $this->docker->containerStart($this->name);

        Partyline::info("SSH available locally on port {$this->getSshPort()}");

        return $this;
    }

    public function stop(): self
    {
        if ($this->connections->count() <= 1) {
            Partyline::comment("Last client on {$this->getName()} disconnected. Shutting down container.");

            $this->kill()->remove();
        }

        return $this;
    }

    public function kill(): self
    {
        $this->docker->containerKill($this->name);

        return $this;
    }

    public function remove(): self
    {
        $deleteAssociatedVolumes = true;

        $response = $this->docker->containerDelete($this->name, [
            'v' => $deleteAssociatedVolumes,
            'force' => true,
        ]);

        return $this;
    }

    public function writeData($data)
    {
        $this->attachToWebSocket();

        $this->webSocket->send($data);
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

        $connection = new WebSocketConnection($stream);

        $this->webSocket = new WebSocket($connection, new Response, new Request('GET', '/ws'));

        return $this;
    }
}
