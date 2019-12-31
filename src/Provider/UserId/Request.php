<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Provider\UserId;

use Laminas\Stdlib\RequestInterface;

class Request implements UserIdProviderInterface
{
    /**
     * Use the composed request to fetch the identity from the query string
     * argument "user_id".
     *
     * @param RequestInterface $requst
     * @return mixed
     */
    public function __invoke(RequestInterface $request)
    {
        return $request->getQuery('user_id', null);
    }
}
