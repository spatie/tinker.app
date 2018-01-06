<?php

namespace App\Services\Docker;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Stream\DuplexResourceStream;
use React\Stream\Util;
use React\Stream\WritableResourceStream;
use React\Stream\WritableStreamInterface;

class WebSocketConnection extends EventEmitter implements ConnectionInterface
{
    /** @var \resource */
    public $stream;

    /** @var \React\Stream\DuplexResourceStream */
    private $input;

    public function __construct($resource, LoopInterface $loop)
    {
        $this->input = new DuplexResourceStream(
            $resource,
            $loop,
            null,
            new WritableResourceStream($resource, $loop, null, null)
        );

        $this->stream = $resource;

        Util::forwardEvents($this->input, $this, ['data', 'end', 'error', 'close', 'pipe', 'drain']);

        $this->input->on('close', [$this, 'close']);
    }

    public function isReadable()
    {
        return $this->input->isReadable();
    }

    public function isWritable()
    {
        return $this->input->isWritable();
    }

    public function pause()
    {
        $this->input->pause();
    }

    public function resume()
    {
        $this->input->resume();
    }

    public function pipe(WritableStreamInterface $dest, array $options = [])
    {
        return $this->input->pipe($dest, $options);
    }

    public function write($data)
    {
        return $this->input->write($data);
    }

    public function end($data = null)
    {
        $this->input->end($data);
    }

    public function close()
    {
        $this->input->close();

        $this->handleClose();

        $this->removeAllListeners();
    }

    public function handleClose()
    {
        if (!is_resource($this->stream)) {
            return;
        }

        // Try to cleanly shut down socket and ignore any errors in case other
        // side already closed. Shutting down may return to blocking mode on
        // some legacy versions, so reset to non-blocking just in case before
        // continuing to close the socket resource.
        // Underlying Stream implementation will take care of closing file
        // handle, so we otherwise keep this open here.
        @stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
        stream_set_blocking($this->stream, false);
    }

    public function getRemoteAddress()
    {
        return $this->parseAddress(@stream_socket_get_name($this->stream, true));
    }

    public function getLocalAddress()
    {
        return $this->parseAddress(@stream_socket_get_name($this->stream, false));
    }

    protected function parseAddress($address)
    {
        if ($address === false) {
            return null;
        }

        if ($address === '') {
            return null;
        }

        if ($address[0] === "\x00") {
            return null;
        }

        return 'unix://' . $address;
    }
}
