<?php

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
