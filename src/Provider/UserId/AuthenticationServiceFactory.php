<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Interop\Container\ContainerInterface; // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid
use Laminas\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory
{
    /**
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if ($container->has(\Laminas\Authentication\AuthenticationService::class)) {
            return new AuthenticationService(
                $container->get(\Laminas\Authentication\AuthenticationService::class),
                $config
            );
        }

        return new AuthenticationService(null, $config);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return AuthenticationService
     */
    public function createService($container)
    {
        return $this($container);
    }
}
