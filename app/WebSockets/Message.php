<?php

namespace App\WebSockets;

use App\Docker\Container;
use Illuminate\Contracts\Support\Jsonable;

class Message implements Jsonable
{
    const SESSION_START_TYPE = 'session-start';
    const TERMINAL_DATA_TYPE = 'terminal-data';
    const BUFFER_RUN_TYPE = 'buffer-run';
    const BUFFER_CHANGE_TYPE = 'buffer-change';

    /** @var int */
    protected $type;

    /** @var string */
    protected $payload;

    /** @var Connection */
    protected $from;

    public function __construct(string $type, string $payload, ?Connection $from = null)
    {
        $this->type = $type;
        $this->payload = $payload;
        $this->from = $from;
    }

    public static function create(string $type, string $payload): self
    {
        return new static(...func_get_args());
    }

    public static function terminalData(string $payload): self
    {
        return new static(static::TERMINAL_DATA_TYPE, $payload);
    }

    public static function bufferChange(string $payload) : self
    {
        return new static(static::BUFFER_CHANGE_TYPE, $payload);
    }

    public static function fromJson(string $json, Connection $fromConnection): self
    {
        $data = json_decode($json);

        return new static(
            $data->type ?? static::TERMINAL_DATA_TYPE,
            is_object($data->payload) ? json_encode($data->payload) : (string) $data->payload,
            $fromConnection
        );
    }

    public function from(): Connection
    {
        return $this->from;
    }

    public function toJson($options = 0): string
    {
        return json_encode([
            'type' => $this->type,
            'payload' => $this->payload,
        ], $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
