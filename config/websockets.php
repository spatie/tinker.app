<?php

return [
    'host' => env('WS_HOST'),
    'port' => env('WS_PORT', 8080),
    'allowedOrigins' => [
        'tinker.sh',
        'tinker.sh.test',
        'localhost',
    ],
];
