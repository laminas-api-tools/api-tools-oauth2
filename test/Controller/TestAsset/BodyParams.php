<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\OAuth2\Controller\TestAsset;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class BodyParams extends AbstractPlugin
{
    public function __invoke(): array
    {
        return [];
    }
}
