<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2;

class Module
{
    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
