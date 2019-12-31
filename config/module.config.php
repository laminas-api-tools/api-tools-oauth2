<?php
return array(
    'controllers' => array(
        'factories' => array(
            'Laminas\ApiTools\OAuth2\Controller\Auth' => 'Laminas\ApiTools\OAuth2\Factory\AuthControllerFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'oauth' => array(
                'type' => 'Laminas\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/oauth',
                    'defaults' => array(
                        'controller' => 'Laminas\ApiTools\OAuth2\Controller\Auth',
                        'action'     => 'token',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'authorize' => array(
                        'type' => 'Laminas\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/authorize',
                            'defaults' => array(
                                'action' => 'authorize',
                            ),
                        ),
                    ),
                    'resource' => array(
                        'type' => 'Laminas\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/resource',
                            'defaults' => array(
                                'action' => 'resource',
                            ),
                        ),
                    ),
                    'code' => array(
                        'type' => 'Laminas\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/receivecode',
                            'defaults' => array(
                                'action' => 'receiveCode',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Laminas\ApiTools\OAuth2\Adapter\PdoAdapter'   => 'Laminas\ApiTools\OAuth2\Factory\PdoAdapterFactory',
            'Laminas\ApiTools\OAuth2\Adapter\MongoAdapter' => 'Laminas\ApiTools\OAuth2\Factory\MongoAdapterFactory',
            'Laminas\ApiTools\OAuth2\Service\OAuth2Server' => 'Laminas\ApiTools\OAuth2\Factory\OAuth2ServerFactory'
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'oauth/authorize'    => __DIR__ . '/../view/laminas/auth/authorize.phtml',
            'oauth/receive-code' => __DIR__ . '/../view/laminas/auth/receive-code.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'api-tools-oauth2' => array(
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
    ),
    'api-tools-content-negotiation' => array(
        'Laminas\ApiTools\OAuth2\Controller\Auth' => array(
            'Laminas\ApiTools\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
            'Laminas\View\Model\ViewModel' => array(
                'text/html',
                'application/xhtml+xml',
            ),
        ),
    ),
);
