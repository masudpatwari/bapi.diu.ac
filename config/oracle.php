<?php

return [
    'oracle' => [
        'driver'         => 'oracle',
        'tns'            => env('O_TNS', '10.11.12.91/orcl'),
        'host'           => env('O_HOST', '10.11.12.91'),
#        'port'           => env('DB_PORT', '1521'),
        'port'           => env('O_PORT', ''),
        'database'       => env('O_DATABASE', 'orcl'),
        'username'       => env('O_USERNAME', 'test_db'),
        'password'       => env('O_PASSWORD', 'test_db12'),
        'charset'        => env('O_CHARSET', 'AL32UTF8'),
        'prefix'         => env('O_PREFIX', ''),
        'prefix_schema'  => env('O_SCHEMA_PREFIX', 'test_db'),
        'server_version' => env('O_SERVER_VERSION', '11g'),
    ],
];
