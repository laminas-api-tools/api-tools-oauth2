<?php // phpcs:disable

return array(
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'api-tools-oauth2' => array(
        'storage' => \Laminas\ApiTools\OAuth2\Adapter\PdoAdapter::class,
        'db' => array(
            'dsn' => 'sqlite::memory:',
        ),
        'allow_implicit' => true,
        'enforce_state'  => true,
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'service_manager' => array(
        'aliases' => array(
            'translator' => 'MvcTranslator',
            'Laminas\ApiTools\OAuth2\Provider\UserId' => \Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationService::class,
        ),
        'invokables' => array(
            \Laminas\Authentication\AuthenticationService::class => \Laminas\Authentication\AuthenticationService::class,
        ),
        'factories' => array(
            \Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationService::class => \Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationServiceFactory::class,
        ),
    ),
);
