<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2;

return [
    'controllers' => [
        // Legacy Zend Framework aliases
        'aliases' => [
            'ZF\OAuth2\Controller\Auth' => 'Laminas\ApiTools\OAuth2\Controller\Auth',
        ],
        'factories' => [
            'Laminas\ApiTools\OAuth2\Controller\Auth' => Factory\AuthControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'oauth' => [
                'type' => 'literal',
                'options' => [
                    'route'    => '/oauth',
                    'defaults' => [
                        'controller' => 'Laminas\ApiTools\OAuth2\Controller\Auth',
                        'action'     => 'token',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'revoke' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/revoke',
                            'defaults' => [
                                'action' => 'revoke',
                            ],
                        ],
                    ],
                    'authorize' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/authorize',
                            'defaults' => [
                                'action' => 'authorize',
                            ],
                        ],
                    ],
                    'resource' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/resource',
                            'defaults' => [
                                'action' => 'resource',
                            ],
                        ],
                    ],
                    'code' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/receivecode',
                            'defaults' => [
                                'action' => 'receiveCode',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'Laminas\ApiTools\OAuth2\Provider\UserId' => Provider\UserId\AuthenticationService::class,

            // Legacy Zend Framework aliases
            'ZF\OAuth2\Provider\UserId' => 'Laminas\ApiTools\OAuth2\Provider\UserId',
            \ZF\OAuth2\Adapter\PdoAdapter::class => Adapter\PdoAdapter::class,
            \ZF\OAuth2\Adapter\IbmDb2Adapter::class => Adapter\IbmDb2Adapter::class,
            \ZF\OAuth2\Adapter\MongoAdapter::class => Adapter\MongoAdapter::class,
            \ZF\OAuth2\Provider\UserId\AuthenticationService::class => Provider\UserId\AuthenticationService::class,
            'ZF\OAuth2\Service\OAuth2Server' => 'Laminas\ApiTools\OAuth2\Service\OAuth2Server',
        ],
        'factories' => [
            Adapter\PdoAdapter::class    => Factory\PdoAdapterFactory::class,
            Adapter\IbmDb2Adapter::class => Factory\IbmDb2AdapterFactory::class,
            Adapter\MongoAdapter::class  => Factory\MongoAdapterFactory::class,
            Provider\UserId\AuthenticationService::class => Provider\UserId\AuthenticationServiceFactory::class,
            'Laminas\ApiTools\OAuth2\Service\OAuth2Server'  => Factory\OAuth2ServerFactory::class
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'oauth/authorize'    => __DIR__ . '/../view/laminas/auth/authorize.phtml',
            'oauth/receive-code' => __DIR__ . '/../view/laminas/auth/receive-code.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'api-tools-oauth2' => [
        /*
         * Config can include:
         * - 'storage' => 'name of storage service' - typically Laminas\ApiTools\OAuth2\Adapter\PdoAdapter
         * - 'db' => [ // database configuration for the above PdoAdapter
         *       'dsn'      => 'PDO DSN',
         *       'username' => 'username',
         *       'password' => 'password'
         *   ]
         * - 'storage_settings' => [ // configuration to pass to the storage adapter
         *       // see https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/Pdo.php#L57-L66
         *   ]
         */
        'grant_types' => [
            'client_credentials' => true,
            'authorization_code' => true,
            'password'           => true,
            'refresh_token'      => true,
            'jwt'                => true,
        ],
        /*
         * Error reporting style
         *
         * If true, client errors are returned using the
         * application/problem+json content type,
         * otherwise in the format described in the oauth2 specification
         * (default: true)
         */
        'api_problem_error_response' => true,
    ],
    'api-tools-content-negotiation' => [
        'controllers' => [
            'Laminas\ApiTools\OAuth2\Controller\Auth' => [
                'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
                    'application/json',
                    'application/*+json',
                ],
                'Laminas\View\Model\ViewModel' => [
                    'text/html',
                    'application/xhtml+xml',
                ],
            ],
        ],
    ],
];
