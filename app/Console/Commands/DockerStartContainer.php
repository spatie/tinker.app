<?php

namespace App\Console\Commands;

use Docker\API\Model\ContainerConfig;
use Docker\Docker;
use Illuminate\Console\Command;

class DockerStartContainer extends Command
{
    protected $signature = 'docker:start-container';
    
    protected $description = 'Start new Docker container';
    
    public function handle()
    {
        $docker = new Docker();
        $containerManager = $docker->getContainerManager();
        
        $containerName = 'tinker-'.str_random();
        
        $containerConfig = new ContainerConfig();
        $containerConfig->setImage('spatie/tinker.sh-docker-image');
        $containerConfig->setCmd(['/usr/bin/php', '/var/www/artisan', 'tinker']);
        // $containerConfig->setCmd(['/bin/bash']);
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


        // Attach endpoint works => but not with TTY (docker-php parses frames wrong)
        $attachStream = $containerManager->attach($containerName, [
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



        // Websocket API doesnt (on mac -> see gh issue)
        $webSocketStream = $containerManager->attachWebsocket($containerName, [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
            'stdin'  => true,
        ]);

        dump($webSocketStream->read());

        $webSocketStream->write('echo "jo"\n');
        do {
            $line = $webSocketStream->read();
            echo($line);
        } while ($line !== null);
    }
}
