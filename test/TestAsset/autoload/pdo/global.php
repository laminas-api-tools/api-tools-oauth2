<?php

declare(strict_types=1);

use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;

return [
    'view_manager'     => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => [
            'layout/layout' => __DIR__ . '/../../view/layout/layout.phtml',
            'error/404'     => __DIR__ . '/../../view/error/404.phtml',
            'error/index'   => __DIR__ . '/../../view/error/index.phtml',
        ],
        'template_path_stack'      => [
            __DIR__ . '/../view',
        ],
    ],
    'api-tools-oauth2' => [
        'storage'        => PdoAdapter::class,
        'db'             => [
            'dsn' => 'sqlite::memory:',
        ],
        'allow_implicit' => true,
        'enforce_state'  => true,
    ],
];
