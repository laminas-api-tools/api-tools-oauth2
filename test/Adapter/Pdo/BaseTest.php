<?php

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

use ReflectionProperty;

abstract class BaseTest extends \Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestAsset/pdo.application.config.php'
        );

        parent::setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $serviceManager->setAllowOverride(true);
    }

    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $pdo = $serviceManager->get('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter');

        $r = new ReflectionProperty($pdo, 'db');
        $r->setAccessible(true);
        $db = $r->getValue($pdo);

        $sql = file_get_contents(__DIR__ . '/../../TestAsset/database/pdo.sql');
        $db->exec($sql);

        return [[$pdo]];
    }
}
