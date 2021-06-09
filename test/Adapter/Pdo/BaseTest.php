<?php // phpcs:disable WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ReflectionProperty;

use function file_get_contents;

abstract class BaseTest extends AbstractHttpControllerTestCase
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

    /** @psalm-return array<array-key, array{0: PdoAdapter}> */
    public function provideStorage(): array
    {
        $this->setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $pdo            = $serviceManager->get(PdoAdapter::class);

        $r = new ReflectionProperty($pdo, 'db');
        $r->setAccessible(true);
        $db = $r->getValue($pdo);

        $sql = file_get_contents(__DIR__ . '/../../TestAsset/database/pdo.sql');
        $db->exec($sql);

        return [[$pdo]];
    }
}
