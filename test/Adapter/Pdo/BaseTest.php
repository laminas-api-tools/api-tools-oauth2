<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

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

        copy(
            __DIR__ . '/../../TestAsset/database/pdo.db',
            sys_get_temp_dir() . '/pdo-test.db'
        );
    }

    protected function tearDown()
    {
        $db = sys_get_temp_dir() . '/pdo-test.db';
        if (file_exists($db)) {
            unlink($db);
        }
    }

    public function provideStorage()
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $pdo = $serviceManager->get('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter');

        return array(array($pdo));
    }
}
