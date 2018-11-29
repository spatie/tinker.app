<?php

namespace App\WebSockets;

use Illuminate\Container\Container;

class MessageDispatcher
{
    /** @var array */
    protected $handlers = [];

    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @param \Closure|string $handler
     */
    public function handle(string $type, $handler)
    {
        $this->handlers[$type][] = $this->makeHandler($handler);
    }

    public function dispatch(Message $message)
    {
        foreach ($this->getHandlers($message) as $handler) {
            $handler($message);
        }
    }

    /**
     * @param \Closure|string $handler
     *
     * @return \Closure
     */
    protected function makeHandler($handler): \Closure
    {
        if (is_string($handler)) {
            return function (Message $message) use ($handler) {
                return $this->container->make($handler)($message);
            };
        }

        return $handler;
    }

    protected function getHandlers(Message $message): array
    {
        return $this->handlers[$message->getType()] ?? [];
    }
}
