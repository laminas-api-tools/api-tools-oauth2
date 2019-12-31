<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Controller;

use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter as OAuth2Storage;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server as OAuth2Server;

class AuthControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator()->get('ServiceManager');
        $config   = $services->get('Configuration');

        if (!isset($config['api-tools-oauth2']['db']) || empty($config['api-tools-oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'api-tools-oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $username = isset($config['api-tools-oauth2']['db']['username']) ? $config['api-tools-oauth2']['db']['username'] : null;
        $password = isset($config['api-tools-oauth2']['db']['password']) ? $config['api-tools-oauth2']['db']['password'] : null;

        $storage = new OAuth2Storage(array(
            'dsn'      => $config['api-tools-oauth2']['db']['dsn'],
            'username' => $username,
            'password' => $password,
        ));

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
