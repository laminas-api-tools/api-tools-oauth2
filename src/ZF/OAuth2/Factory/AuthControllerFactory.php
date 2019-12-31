<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Controller\AuthController;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server as OAuth2Server;

class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllers
     * @return AuthController
     * @throws \Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator()->get('ServiceManager');
        $config   = $services->get('Configuration');

        if (!isset($config['api-tools-oauth2']['storage']) || empty($config['api-tools-oauth2']['storage'])) {
            throw new Exception\RuntimeException(
                'The storage configuration [\'api-tools-oauth2\'][\'storage\'] for OAuth2 is missing'
            );
        }

        $storage = $services->get($config['api-tools-oauth2']['storage']);

        $enforceState  = isset($config['api-tools-oauth2']['enforce_state'])  ? $config['api-tools-oauth2']['enforce_state']  : true;
        $allowImplicit = isset($config['api-tools-oauth2']['allow_implicit']) ? $config['api-tools-oauth2']['allow_implicit'] : false;

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $server = new OAuth2Server($storage, array('enforce_state' => $enforceState, 'allow_implicit' => $allowImplicit));

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $server->addGrantType(new ClientCredentials($storage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $server->addGrantType(new AuthorizationCode($storage));

        // Add the "User Credentials" grant type
        $server->addGrantType(new UserCredentials($storage));

        // Add the "Refresh Token" grant type
        $server->addGrantType(new RefreshToken($storage));

        return new AuthController($server);
    }
}
