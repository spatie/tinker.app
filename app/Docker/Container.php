<?php

namespace App\Docker;

use App\Models\Container as ContainerModel;
use Closure;
use Docker\Docker;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Ratchet\Client\WebSocket;
use stdClass;
use Wilderborn\Partyline\Facade as Partyline;

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

    public function __construct(string $name, ?Docker $docker = null)
    {
        $this->name = $name;

        $this->docker = $docker ?? app(Docker::class);

        $this->containerModel = ContainerModel::updateOrCreate([
            'name' => $name,
        ], [
            'active' => true,
        ]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInfo(): stdClass
    {
        $response = $this->docker->containerInspect($this->getName(), [], Docker::FETCH_RESPONSE);

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

    public function getSessionData(): array
    {
        return [
            'sessionId' => $this->getName(),
            'code' => $this->getContainerModel()->code,
        ];
    }

    public function start(): self
    {
        $this->docker->containerStart($this->name);

        Partyline::info("SSH available locally on port {$this->getSshPort()}");

        return $this;
    }

    public function stop(): self
    {
        $this->kill()->remove();

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

        $this->docker->containerDelete($this->name, [
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
        ], Docker::FETCH_RESPONSE);

        $stream = $response->getBody()->detach();

        $connection = new WebSocketConnection($stream);

        $this->webSocket = new WebSocket($connection, new Response, new Request('GET', '/ws'));

        return $this;
    }
}
