<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException;
use Laminas\ApiTools\OAuth2\Factory\MongoAdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use MongoClient;
use MongoDB;
use ReflectionObject;

use function class_exists;
use function extension_loaded;
use function version_compare;

class MongoAdapterFactoryTest extends AbstractHttpControllerTestCase
{
    /** @var MongoAdapterFactory */
    protected $factory;

    /** @var ServiceManager */
    protected $services;

    protected function setUp(): void
    {
        if (
            ! (extension_loaded('mongodb') || extension_loaded('mongo'))
            || ! class_exists(MongoClient::class)
            || version_compare(MongoClient::VERSION, '1.4.1', '<')
        ) {
            $this->markTestSkipped('ext/mongo or ext/mongodb + alcaeus/mongo-php-adapter is not available.');
        }

        $this->factory  = new MongoAdapterFactory();
        $this->services = new ServiceManager();

        $this->setApplicationConfig([
            'modules'                  => [
                'Laminas\ApiTools\OAuth2',
            ],
            'module_listener_options'  => [
                'module_paths'      => [__DIR__ . '/../../'],
                'config_glob_paths' => [],
            ],
            'service_listener_options' => [],
            'service_manager'          => [],
        ]);
        parent::setUp();
    }

    public function testExceptionThrownWhenMissingMongoCredentials(): void
    {
        $this->services->setService('config', []);
        $this->expectException(RuntimeException::class);
        ($this->factory)($this->services);
    }

    public function testInstanceCreated(): void
    {
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'mongo' => [
                    'database' => 'test',
                    'dsn'      => 'mongodb://127.0.0.1:27017',
                ],
            ],
        ]);

        $adapter = ($this->factory)($this->services);
        $this->assertInstanceOf(MongoAdapter::class, $adapter);
    }

    public function testInstanceCreatedWithMongoDbInServiceLocator(): void
    {
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'mongo' => [
                    'locator_name' => 'testdb',
                ],
            ],
        ]);
        $mock = $this->getMockBuilder(MongoDB::class, [], [], '', false)
            ->disableOriginalConstructor()
            ->getMock();
        $this->services->setService('testdb', $mock);

        $adapter = ($this->factory)($this->services);
        $this->assertInstanceOf(MongoAdapter::class, $adapter);
    }

    public function testCanPassAdapterConfigurationWhenCreatingInstance(): void
    {
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'mongo'            => [
                    'locator_name' => 'testdb',
                ],
                'storage_settings' => [
                    'user_table' => 'my_users',
                ],
            ],
        ]);
        $mock = $this->getMockBuilder(MongoDB::class, [], [], '', false)
            ->disableOriginalConstructor()
            ->getMock();
        $this->services->setService('testdb', $mock);

        $adapter = ($this->factory)($this->services);
        $this->assertInstanceOf(MongoAdapter::class, $adapter);

        $r = new ReflectionObject($adapter);
        $c = $r->getProperty('config');
        $c->setAccessible(true);
        $config = $c->getValue($adapter);
        $this->assertEquals('my_users', $config['user_table']);
    }
}
