<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use MongoClient;

/**
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
class MongoAdapterFactory
{
    /**
     * @param  ContainerInterface $container
     * @return MongoAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config  = $container->get('config');
        return new MongoAdapter(
            $this->getMongoDb($container, $config),
            $this->getOauth2ServerConfig($config)
        );
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Laminas\ServiceManager\ServiceLocatorInterface $container
     * @return MongoAdapter
     */
    public function createService($container)
    {
        return $this($container);
    }

    /**
     * Get the mongo database
     *
     * @param ContainerInterface $container
     * @param array|\ArrayAccess $config
     * @return \MongoDB
     */
    protected function getMongoDb(ContainerInterface $container, $config)
    {
        $dbLocatorName = isset($config['api-tools-oauth2']['mongo']['locator_name'])
            ? $config['api-tools-oauth2']['mongo']['locator_name']
            : 'MongoDB';

        if ($container->has($dbLocatorName)) {
            return $container->get($dbLocatorName);
        }

        if (! isset($config['api-tools-oauth2']['mongo'])
            || empty($config['api-tools-oauth2']['mongo']['database'])
        ) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'mongo\'] for OAuth2 is missing'
            );
        }

        $options = isset($config['api-tools-oauth2']['mongo']['options'])
            ? $config['api-tools-oauth2']['mongo']['options']
            : [];
        $options['connect'] = false;
        $server = isset($config['api-tools-oauth2']['mongo']['dsn'])
            ? $config['api-tools-oauth2']['mongo']['dsn']
            : null;
        $mongo = new MongoClient($server, $options);

        return $mongo->{$config['api-tools-oauth2']['mongo']['database']};
    }

    /**
     * Retrieve oauth2-server-php configuration
     *
     * @param array|\ArrayAccess $config
     * @return array
     */
    protected function getOauth2ServerConfig($config)
    {
        if (isset($config['api-tools-oauth2']['storage_settings'])
            && is_array($config['api-tools-oauth2']['storage_settings'])
        ) {
            return $config['api-tools-oauth2']['storage_settings'];
        }

        return [];
    }
}
