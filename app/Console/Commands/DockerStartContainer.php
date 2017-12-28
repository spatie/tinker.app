<?php

namespace App\Console\Commands;

use Docker\API\Model\ContainersCreatePostBody;
use Docker\Docker;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Console\Command;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use React\Stream\DuplexResourceStream;

class DockerStartContainer extends Command
{
    protected $signature = 'docker:start-container {--W|websockets}';

    protected $description = 'Start new Docker container';

    public function handle()
    {
        $docker = Docker::create();

        $containerName = 'tinker-'.str_random();

        $containerCreatePostBody = new ContainersCreatePostBody();
        $containerCreatePostBody->setImage('alexvanderbist/tinker-sh-image');
        // $containerCreatePostBody->setCmd(['/usr/bin/php', '/var/www/artisan', 'tinker']); // is in image now
        $containerCreatePostBody->setTty(true);
        $containerCreatePostBody->setOpenStdin(true); // -i interactive flag = keep stdin open even when not attached
        // $containerCreatePostBody->setStdinOnce(true); // close stdin after client dc
        $containerCreatePostBody->setAttachStdin(true);
        $containerCreatePostBody->setAttachStdout(true);
        $containerCreatePostBody->setAttachStderr(true);

        $docker->containerCreate($containerCreatePostBody, ['name' => $containerName]);

        $this->comment($containerName);

        // start container
        $docker->containerStart($containerName);

        if ($this->option('websockets')) {
            return $this->listenToWebsockets($docker, $containerName);
        }

        $this->listenToHijackedRequest($docker, $containerName);
    }

    protected function listenToHijackedRequest($docker, string $containerName)
    {
        // Attach endpoint works => but not with TTY (docker-php parses frames wrong)
        $attachStream = $docker->containerAttach($containerName, [
            'stream' => true,
            'stdin' => true,
            'stdout' => true,
            'stderr' => true
        ]);

        $attachStream->onStdout(function ($stdout) {
            echo $stdout;
        });

        $attachStream->onStderr(function ($stderr) {
            echo $stderr;
        });

        $attachStream->wait();

        // This works for getting an interactive stream tho
        $stream = $attachStream->getBody()->detach();

        /** @var \Http\Client\Socket\Stream */
        $stream = Psr7\stream_for($stream);

        // dd($stream->isWritable());
    }

    protected function listenToWebsockets($docker, string $containerName)
    {

        // Websocket API doesnt work on mac -> see gh issue

        $response = $docker->containerAttachWebsocket($containerName, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ], false);

        // This works for getting an interactive stream tho
        $stream = $response->getBody()->detach();

        $loop = Factory::create();

        $conn = new DuplexResourceStream($stream, $loop);

        $conn->on('data', function ($data) {
            echo $data;
        });

        $loop->run();
    }
}
