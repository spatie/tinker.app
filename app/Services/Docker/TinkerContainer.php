<?php

namespace App\Services\Docker;

use Docker\API\Model\ContainersCreatePostBody;
use Docker\Docker;

class TinkerContainer
{
    /** @var string */
    protected $name;

    /** @var \Docker\Docker */
    protected $docker;

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

    public function getWebsocketStream()
    {
        // Websocket API doesnt (on mac -> see gh issue)
        $response = $docker->containerAttach($containerName, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ], false);

        $stream = $response->getBody()->detach();

        /** @var \Http\Client\Socket\Stream */
        $stream = Psr7\stream_for($stream);

        // dd($stream->isWritable());
        dd($stream);

        while (true) {
            // $stream->write('ejo');
            echo $stream->read(8);
        }
    }
}
