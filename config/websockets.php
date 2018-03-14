<?php

return [
    'host' => env('WS_HOST', 'localhost'),
    'port' => env('WS_PORT', 8080),
    'allowedOrigins' => [
        '165.227.172.206',
        'artisan.sh',
        'artisan.sh.test',
        'localhost',
    ],
];
