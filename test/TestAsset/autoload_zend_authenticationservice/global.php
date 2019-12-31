<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

// @codingStandardsIgnoreFile
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
        'storage' => 'Laminas\ApiTools\OAuth2\Adapter\PdoAdapter',
        'db' => array(
            'dsn' => 'sqlite:' . sys_get_temp_dir() . '/dbtest.sqlite',
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
            'Laminas\ApiTools\OAuth2\Provider\UserId' => 'Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationService',
        ),
        'invokables' => array(
            'Laminas\Authentication\AuthenticationService' => 'Laminas\Authentication\AuthenticationService',
        ),
        'factories' => array(
            'Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationService' => 'Laminas\ApiTools\OAuth2\Provider\UserId\AuthenticationServiceFactory',
        ),
    ),
);
