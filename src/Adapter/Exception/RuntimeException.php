<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Adapter\Exception;

use Laminas\ApiTools\OAuth2\ExceptionInterface;

class RuntimeException extends \RuntimeException implements
    ExceptionInterface
{
}
