<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use MongoClient;

/**
 * Class MongoAdapterFactory
 *
 * @package Laminas\ApiTools\OAuth2\Factory
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
class MongoAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws Exception\RuntimeException
     * @return MongoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config  = $services->get('Config');
        return new MongoAdapter($this->getMongoDb($services), $this->getOauth2ServerConfig($config));
    }

    /**
     * Get the mongo database
     *
     * @param ServiceLocatorInterface $services
     * @return \MongoDB
     */
    protected function getMongoDb($services)
    {
        $config  = $services->get('Config');
        $dbLocatorName = isset($config['api-tools-oauth2']['mongo']['locator_name'])
            ? $config['api-tools-oauth2']['mongo']['locator_name']
            : 'MongoDB';

        if ($services->has($dbLocatorName)) {
            return $services->get($dbLocatorName);
        }

        if (!isset($config['api-tools-oauth2']['mongo']) || empty($config['api-tools-oauth2']['mongo']['database'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'mongo\'] for OAuth2 is missing'
            );
        }

        $options = isset($config['api-tools-oauth2']['mongo']['options']) ? $config['api-tools-oauth2']['mongo']['options'] : [];
        $options['connect'] = false;
        $server  = isset($config['api-tools-oauth2']['mongo']['dsn']) ? $config['api-tools-oauth2']['mongo']['dsn'] : null;
        $mongo   = new MongoClient($server, $options);
        return $mongo->{$config['api-tools-oauth2']['mongo']['database']};
    }

    /**
     * Retrieve oauth2-server-php configuration
     *
     * @return array
     */
    protected function getOauth2ServerConfig($config)
    {
        $oauth2ServerConfig = [];
        if (isset($config['api-tools-oauth2']['storage_settings']) && is_array($config['api-tools-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['api-tools-oauth2']['storage_settings'];
        }

        return $oauth2ServerConfig;
    }
}
