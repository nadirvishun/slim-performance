<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../runtime/logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        //Eloquent Database
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'performance',
            'username' => 'root',
            'password' => 'root',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],

        //twig
        'view'=>[
            'template_path' => __DIR__ . '/templates/',
            'cache_path'=> __DIR__ . '/../runtime/cache/',
        ]
    ],
];
