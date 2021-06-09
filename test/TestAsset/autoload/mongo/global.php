<?php

use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;

return [
    'view_manager'     => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'     => __DIR__ . '/../view/error/404.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack'      => [
            __DIR__ . '/../view',
        ],
    ],
    'api-tools-oauth2' => [
        'storage'        => MongoAdapter::class,
        'mongo'          => [
            'dsn' => 'mongodb://localhost:27017',
        ],
        'allow_implicit' => true,
        'enforce_state'  => true,
    ],
];
