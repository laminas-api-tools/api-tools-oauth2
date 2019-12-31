<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Controller\AuthController;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use OAuth2\Server as OAuth2Server;

class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllers
     * @return AuthController
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator()->get('ServiceManager');

        // For BC, if the Laminas\ApiTools\OAuth2\Service\OAuth2Server service returns an
        // OAuth2\Server instance, wrap it in a closure.
        $oauth2ServerFactory = $services->get('Laminas\ApiTools\OAuth2\Service\OAuth2Server');
        if ($oauth2ServerFactory instanceof OAuth2Server) {
            $oauth2Server = $oauth2ServerFactory;
            $oauth2ServerFactory = function () use ($oauth2Server) {
                return $oauth2Server;
            };
        }

        $authController = new AuthController(
            $oauth2ServerFactory,
            $services->get('Laminas\ApiTools\OAuth2\Provider\UserId')
        );

        $config = $services->get('Config');
        $authController->setApiProblemErrorResponse((isset($config['api-tools-oauth2']['api_problem_error_response'])
            && $config['api-tools-oauth2']['api_problem_error_response'] === true));

        return $authController;
    }
}
