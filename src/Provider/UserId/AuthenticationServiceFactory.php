<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');

        if ($services->has('Laminas\Authentication\AuthenticationService')) {
            return new AuthenticationService(
                $services->get('Laminas\Authentication\AuthenticationService'),
                $config
            );
        }

        return new AuthenticationService(null, $config);
    }
}
