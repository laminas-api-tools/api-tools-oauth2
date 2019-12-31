<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class PdoAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws \Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException
     * @return \Laminas\ApiTools\OAuth2\Adapter\PdoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');

        if (!isset($config['api-tools-oauth2']['db']) || empty($config['api-tools-oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $username = isset($config['api-tools-oauth2']['db']['username']) ? $config['api-tools-oauth2']['db']['username'] : null;
        $password = isset($config['api-tools-oauth2']['db']['password']) ? $config['api-tools-oauth2']['db']['password'] : null;

        $oauth2ServerConfig = array();
        if (isset($config['api-tools-oauth2']['storage_settings']) && is_array($config['api-tools-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['api-tools-oauth2']['storage_settings'];
        }

        return new PdoAdapter(array(
            'dsn'      => $config['api-tools-oauth2']['db']['dsn'],
            'username' => $username,
            'password' => $password,
        ), $oauth2ServerConfig);
    }
}
