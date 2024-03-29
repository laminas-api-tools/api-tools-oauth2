<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Stdlib\RequestInterface;

use function is_array;
use function is_object;
use function method_exists;
use function property_exists;
use function ucfirst;

class AuthenticationService implements UserIdProviderInterface
{
    private ?AuthenticationServiceInterface $authenticationService;

    private string $userId = 'id';

    /**
     *  Set authentication service
     *
     * @param array $config
     */
    public function __construct(?AuthenticationServiceInterface $service = null, $config = [])
    {
        $this->authenticationService = $service;

        if (isset($config['api-tools-oauth2']['user_id'])) {
            $this->userId = $config['api-tools-oauth2']['user_id'];
        }
    }

    /**
     * Use implementation of Laminas\Authentication\AuthenticationServiceInterface to fetch the identity.
     *
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
