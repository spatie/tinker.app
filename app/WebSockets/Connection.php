<?php

namespace App\WebSockets;

use App\Docker\Container;
use App\Docker\ContainerInterface;
use App\Docker\NullContainer;
use Ratchet\ConnectionInterface;
use Wilderborn\Partyline\Partyline;

class Connection
{
    /** @var ConnectionInterface */
    protected $browserConnection;

    /** @var ?Container */
    protected $container;

    /** @var WebSocketConnectionRepository */
    protected $webSocketConnectionRepository;

    public function __construct(ConnectionInterface $browserConnection, WebSocketConnectionRepository $webSocketConnectionRepository)
    {
        $this->browserConnection = $browserConnection;

        $this->webSocketConnectionRepository = $webSocketConnectionRepository;

        $this->container = new NullContainer(); // TODO: DI?
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function getBrowserConnection(): ConnectionInterface
    {
        return $this->browserConnection;
    }

    public function usesBrowserConnection(ConnectionInterface $browserConnection): bool
    {
        return $this->browserConnection === $browserConnection;
    }

    public function send(Message $message)
    {
        $this->browserConnection->send($message);
    }

    public function writeToTerminal(string $data)
    {
        $this->send(Message::terminalData($data));
    }

    public function close()
    {
        $this->browserConnection->close();
    }

    public function onClose(): self
    {
        if ($container = $this->getContainer()) {
            if (count($this->webSocketConnectionRepository->getConnectedToContainer($container)) === 1) {
                Partyline::comment("Last client on {$container->getName()} disconnected. Shutting down container.");

                $container->stop();
            }
        }

        return $this;
    }

    public function setCode(string $code): self
    {
        $this->getContainer()->getContainerModel()->update(['code' => $code]);

        $this->getContainer()->sendFileContents('tinker_buffer', $code);

        return $this;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
