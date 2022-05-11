<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Factory;

use ArrayAccess;
use Interop\Container\ContainerInterface; // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid
use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use Laminas\ServiceManager\ServiceLocatorInterface;
use MongoClient;
use MongoDB;

use function is_array;

class MongoAdapterFactory
{
    /**
     * @return MongoAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new MongoAdapter(
            $this->getMongoDb($container, $config),
            $this->getOauth2ServerConfig($config)
        );
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return MongoAdapter
     */
    public function createService($container)
    {
        return $this($container);
    }

    /**
     * Get the mongo database
     *
     * @param array|ArrayAccess $config
     * @return MongoDB
     */
    protected function getMongoDb(ContainerInterface $container, $config)
    {
        $dbLocatorName = $config['api-tools-oauth2']['mongo']['locator_name'] ?? 'MongoDB';

        if ($container->has($dbLocatorName)) {
            return $container->get($dbLocatorName);
        }

        if (
            ! isset($config['api-tools-oauth2']['mongo'])
            || empty($config['api-tools-oauth2']['mongo']['database'])
        ) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'mongo\'] for OAuth2 is missing'
            );
        }

        $options            = $config['api-tools-oauth2']['mongo']['options'] ?? [];
        $options['connect'] = false;
        $server             = $config['api-tools-oauth2']['mongo']['dsn'] ?? null;
        $mongo              = new MongoClient($server, $options);

        return $mongo->{$config['api-tools-oauth2']['mongo']['database']};
    }

    /**
     * Retrieve oauth2-server-php configuration
     *
     * @param array|ArrayAccess $config
     * @return array
     */
    protected function getOauth2ServerConfig($config)
    {
        if (
            isset($config['api-tools-oauth2']['storage_settings'])
            && is_array($config['api-tools-oauth2']['storage_settings'])
        ) {
            return $config['api-tools-oauth2']['storage_settings'];
        }

        return [];
    }
}
