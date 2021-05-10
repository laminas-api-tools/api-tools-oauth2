<?php

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Laminas\Stdlib\RequestInterface;

interface UserIdProviderInterface
{
    /**
     * Return the current authenticated user identifier.
     *
     * @param RequestInterface $request
     * @return mixed
     */
    public function __invoke(RequestInterface $request);
}
