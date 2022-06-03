<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Factory;

use Interop\Container\ContainerInterface; // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid
use Laminas\ServiceManager\ServiceLocatorInterface;

class OAuth2ServerFactory
{
    /**
     * @return OAuth2ServerInstanceFactory
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $config['api-tools-oauth2'] ?? [];
        return new OAuth2ServerInstanceFactory($config, $container);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return OAuth2ServerInstanceFactory
     */
    public function createService($container)
    {
        return $this($container);
    }
}
