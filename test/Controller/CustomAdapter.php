<?php

namespace LaminasTest\ApiTools\OAuth2\Controller;

use Laminas\ApiTools\ApiProblem\Exception\DomainException;
use OAuth2\Storage\Memory;

class CustomAdapter extends Memory
{
    public function checkUserCredentials($username, $password)
    {
        // mocking logic to throw an exception if the user is banned
        if ($username === 'banned_user') {
            $loginException = new DomainException('User is banned', 401);
            $loginException->setTitle('banned');
            $loginException->setType('http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html');
            throw $loginException;
        }

        return parent::checkUserCredentials($username, $password);
    }

    public function isPublicClient($client_id)
    {
        return true;
    }
}
