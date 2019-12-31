<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Laminas\Authentication\AuthenticationService as LaminasAuthenticationService;
use Laminas\Stdlib\RequestInterface;

class AuthenticationService implements UserIdProviderInterface
{
    /**
     * @var LaminasAuthenticationService
     */
    private $authenticationService;

    /**
     *  Set authentication service
     *
     * @param LaminasAuthenticationService $service
     */
    public function __construct(LaminasAuthenticationService $service)
    {
        $this->authenticationService = $service;
    }

    /**
     * Use Laminas\Authentication\AuthenticationService to fetch the identity.
     *
     * @param RequestInterface $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        return $this->authenticationService->getIdentity();
    }
}
