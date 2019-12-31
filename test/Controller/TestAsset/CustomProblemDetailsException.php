<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\OAuth2\Controller\TestAsset;

use RuntimeException;
use Laminas\ApiTools\ApiProblem\Exception\ProblemExceptionInterface;

class CustomProblemDetailsException extends RuntimeException implements ProblemExceptionInterface
{
    public $type;
    public $title;
    public $details;

    public function getType()
    {
        return $this->type;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAdditionalDetails()
    {
        return $this->details;
    }
}
