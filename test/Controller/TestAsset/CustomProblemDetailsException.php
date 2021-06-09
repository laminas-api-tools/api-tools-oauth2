<?php

namespace LaminasTest\ApiTools\OAuth2\Controller\TestAsset;

use Laminas\ApiTools\ApiProblem\Exception\ProblemExceptionInterface;
use RuntimeException;

class CustomProblemDetailsException extends RuntimeException implements ProblemExceptionInterface
{
    /** @var string */
    public $type;

    /** @var string */
    public $title;

    /** @var array */
    public $details;

    /** @return string */
    public function getType()
    {
        return $this->type;
    }

    /** @return string */
    public function getTitle()
    {
        return $this->title;
    }

    /** @return array */
    public function getAdditionalDetails()
    {
        return $this->details;
    }
}
