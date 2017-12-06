<?php

namespace App\Console\Commands;

use Docker\API\Model\ContainerConfig;
use Docker\Docker;
use Illuminate\Console\Command;

class DockerStartContainer extends Command
{
    protected $signature = 'docker:start-container {--W|websockets}';
    
    protected $description = 'Start new Docker container';
    
    public function handle()
    {
        $docker = new Docker();
        $containerManager = $docker->getContainerManager();
        
        $containerName = 'tinker-'.str_random();
        
        $containerConfig = new ContainerConfig();
        $containerConfig->setImage('alexvanderbist/tinker-sh-image');
        // $containerConfig->setCmd(['/usr/bin/php', '/var/www/artisan', 'tinker']); // is in image now
        $containerConfig->setTty(true);
        $containerConfig->setOpenStdin(true); // -i interactive flag = keep stdin open even when not attached
        // $containerConfig->setStdinOnce(true); // close stdin after client dc
        $containerConfig->setAttachStdin(true);
        $containerConfig->setAttachStdout(true);
        $containerConfig->setAttachStderr(true);
        
        $containerManager->create($containerConfig, ['name' => $containerName]);

        $this->comment($containerName);
        
        // start container
        $containerManager->start($containerName);

        if ($this->option('websockets')) {
            return $this->listenToWebsockets($docker, $containerName);
        }

        $this->listenToHijackedRequest($docker, $containerName);
    }

    protected function listenToHijackedRequest($docker, string $containerName)
    {
        // Attach endpoint works => but not with TTY (docker-php parses frames wrong)
        $attachStream = $docker->getContainerManager()->attach($containerName, [
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
    }

    protected function listenToWebsockets($docker, string $containerName)
    {
        // Websocket API doesnt (on mac -> see gh issue)
        $webSocketStream = $docker->getContainerManager()->attachWebsocket($containerName, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ]);

        $webSocketStream->write('echo "jo"\n');
        do {
            $line = $webSocketStream->read();
            echo($line);
        } while ($line !== null);
    }
}
