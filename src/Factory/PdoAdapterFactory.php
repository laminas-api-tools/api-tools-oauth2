<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;

class PdoAdapterFactory
{
    /**
     * @param  ContainerInterface $container
     * @return PdoAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (empty($config['api-tools-oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $oauthConfig = $config['api-tools-oauth2'];

        $username = isset($oauthConfig['db']['username']) ? $oauthConfig['db']['username'] : null;
        $password = isset($oauthConfig['db']['password']) ? $oauthConfig['db']['password'] : null;
        $options  = isset($oauthConfig['db']['options']) ? $oauthConfig['db']['options'] : [];

        $oauth2ServerConfig = [];
        if (isset($oauthConfig['storage_settings']) && is_array($oauthConfig['storage_settings'])) {
            $oauth2ServerConfig = $oauthConfig['storage_settings'];
        }

        return new PdoAdapter([
            'dsn'      => $oauthConfig['db']['dsn'],
            'username' => $username,
            'password' => $password,
            'options'  => $options,
        ], $oauth2ServerConfig);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Laminas\ServiceManager\ServiceLocatorInterface $container
     * @return PdoAdapter
     */
    public function createService($container)
    {
        return $this($container);
    }
}
