<?php

return [
    'host' => env('WS_HOST'),
    'port' => env('WS_PORT', 8080),
    'allowedOrigins' => [
        '165.227.172.206',
        'tinker.sh',
        'tinker.sh.test',
        'localhost',
    ],
];
