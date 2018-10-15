<?php

return [
    'host' => env('WS_HOST', 'localhost'),
    'port' => env('WS_PORT', 8080),
    'protocol' => env('WS_PROTOCOL', 'ws'),
    'allowedOrigins' => [
        '165.227.172.206',
        'tinker.app',
        'tinker.app.test',
        'localhost',
    ],
];
