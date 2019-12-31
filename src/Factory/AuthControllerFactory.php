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

class AuthControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllers
     * @return AuthController
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator()->get('ServiceManager');
        $authController = new AuthController(
            $services->get('Laminas\ApiTools\OAuth2\Service\OAuth2Server'),
            $services->get('Laminas\ApiTools\OAuth2\Provider\UserId')
        );

        $config = $services->get('Config');
        $authController->setApiProblemErrorResponse((isset($config['api-tools-oauth2']['api_problem_error_response'])
            && $config['api-tools-oauth2']['api_problem_error_response'] === true));

        return $authController;
    }
}
