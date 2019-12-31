<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\ApiTools\OAuth2\Factory;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class OAuth2ServerFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $services
     * @return OAuth2\Server
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');
        $config = isset($config['api-tools-oauth2']) ? $config['api-tools-oauth2'] : array();
        return new OAuth2ServerInstanceFactory($config, $services);
    }
}
