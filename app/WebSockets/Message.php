<?php

namespace App\WebSockets;

use Illuminate\Contracts\Support\Jsonable;

class Message implements Jsonable
{
    const TERMINAL_DATA_TYPE = 'terminal-data';
    const BUFFER_RUN_TYPE = 'buffer-run';
    const BUFFER_CHANGE_TYPE = 'buffer-change';

    /** @var int */
    protected $type;

    /** @var string */
    protected $payload;

    public function __construct(string $type, string $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    public static function create(string $type, string $payload): self
    {
        return new static(func_get_args());
    }

    public static function terminalData(string $payload): self
    {
        return new static(static::TERMINAL_DATA_TYPE, $payload);
    }

    public static function bufferChange(string $payload) : self
    {
        return new static(static::BUFFER_CHANGE_TYPE, $payload);
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json);

        return new static(
            $data->type ?? static::TERMINAL_DATA_TYPE,
            is_object($data->payload) ? json_encode($data->payload) : (string) $data->payload
        );
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
