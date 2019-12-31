<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\OAuth2\Adapter\IbmDb2Adapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;

class IbmDb2AdapterFactory
{
    /**
     * @param  ContainerInterface $container
     * @return IbmDb2Adapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (! isset($config['api-tools-oauth2']['db']) || empty($config['api-tools-oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $username = isset($config['api-tools-oauth2']['db']['username'])
            ? $config['api-tools-oauth2']['db']['username']
            : null;
        $password = isset($config['api-tools-oauth2']['db']['password'])
            ? $config['api-tools-oauth2']['db']['password']
            : null;
        $driver_options  = isset($config['api-tools-oauth2']['db']['driver_options'])
            ? $config['api-tools-oauth2']['db']['driver_options']
            : [];

        $oauth2ServerConfig = [];
        if (isset($config['api-tools-oauth2']['storage_settings'])
            && is_array($config['api-tools-oauth2']['storage_settings'])
        ) {
            $oauth2ServerConfig = $config['api-tools-oauth2']['storage_settings'];
        }

        return new IbmDb2Adapter([
            'database'       => $config['api-tools-oauth2']['db']['database'],
            'username'       => $username,
            'password'       => $password,
            'driver_options' => $driver_options,
        ], $oauth2ServerConfig);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Laminas\ServiceManager\ServiceLocatorInterface $container
     * @return IbmDb2Adapter
     */
    public function createService($container)
    {
        return $this($container);
    }
}
