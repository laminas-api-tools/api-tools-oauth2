<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Stdlib\RequestInterface;

class AuthenticationService implements UserIdProviderInterface
{
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var string
     */
    private $userId = 'id';

    /**
     *  Set authentication service
     *
     * @param AuthenticationServiceInterface $service
     * @param array $config
     */
    public function __construct(AuthenticationServiceInterface $service = null, $config = [])
    {
        $this->authenticationService = $service;

        if (isset($config['api-tools-oauth2']['user_id'])) {
            $this->userId = $config['api-tools-oauth2']['user_id'];
        }
    }

    /**
     * Use implementation of Laminas\Authentication\AuthenticationServiceInterface to fetch the identity.
     *
     * @param  RequestInterface $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        if (null === $this->authenticationService) {
            return null;
        }

        $identity = $this->authenticationService->getIdentity();

        if (is_object($identity)) {
            if (property_exists($identity, $this->userId)) {
                return $identity->{$this->userId};
            }

            $method = "get" . ucfirst($this->userId);
            if (method_exists($identity, $method)) {
                return $identity->$method();
            }

            return null;
        }

        if (is_array($identity) && isset($identity[$this->userId])) {
            return $identity[$this->userId];
        }

        return null;
    }
}
