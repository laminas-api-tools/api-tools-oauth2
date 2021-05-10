<?php

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Interop\Container\ContainerInterface;

class AuthenticationServiceFactory
{
    /**
     * @param  ContainerInterface $container
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if ($container->has('Laminas\Authentication\AuthenticationService')) {
            return new AuthenticationService(
                $container->get('Laminas\Authentication\AuthenticationService'),
                $config
            );
        }

        return new AuthenticationService(null, $config);
    }

    /**
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param \Laminas\ServiceManager\ServiceLocatorInterface $container
     * @return AuthenticationService
     */
    public function createService($container)
    {
        return $this($container);
    }
}
