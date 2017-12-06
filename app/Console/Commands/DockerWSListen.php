<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Wrench\Client;

class DockerWSListen extends Command
{
    protected $signature = 'docker:listen {containerName}';

    protected $description = 'Command description';

    public function handle()
    {
        $containerName = $this->argument('containerName');
        $client = new Client("ws://unix:///var/run/docker.sock/containers/{$containerName}/attach/ws?stream=1&stdin=1&stdout=1", 'http://localhost');
        $client->connect();
        while (true) {
            $client->sendData('hello');
        }
        // $response = $client->receive()[0]->getPayload();
        $client->disconnect();
    }
}
