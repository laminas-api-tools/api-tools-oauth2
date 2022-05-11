<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Factory;

use Interop\Container\ContainerInterface; // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid
use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function is_array;

class PdoAdapterFactory
{
    /**
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

        $username = $oauthConfig['db']['username'] ?? null;
        $password = $oauthConfig['db']['password'] ?? null;
        $options  = $oauthConfig['db']['options'] ?? [];

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
     * @param ServiceLocatorInterface $container
     * @return PdoAdapter
     */
    public function createService($container)
    {
        return $this($container);
    }
}
