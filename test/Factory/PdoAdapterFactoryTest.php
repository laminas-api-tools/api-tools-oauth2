<?php

namespace LaminasTest\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Factory\PdoAdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PDO;
use ReflectionObject;

class PdoAdapterFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var PdoAdapterFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @expectedException \Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownWhenMissingDbCredentials()
    {
        $this->services->setService('config', []);
        $smFactory = $this->factory;
        $adapter = $smFactory($this->services);

        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    public function testInstanceCreated()
    {
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'db' => [
                    'username' => 'foo',
                    'password' => 'bar',
                    'dsn'      => 'sqlite::memory:',
                ],
            ],
        ]);
        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    public function testAllowsPassingOauth2ServerConfigAndPassesOnToUnderlyingAdapter()
    {
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'db' => [
                    'username' => 'foo',
                    'password' => 'bar',
                    'dsn'      => 'sqlite::memory:',
                ],
                'storage_settings' => [
                    'user_table' => 'my_users',
                ],
            ],
        ]);
        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);

        $r = new ReflectionObject($adapter);
        $c = $r->getProperty('config');
        $c->setAccessible(true);
        $config = $c->getValue($adapter);
        $this->assertEquals('my_users', $config['user_table']);
    }

    public function testAllowsPassingDbOptions()
    {
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'db' => [
                    'username' => 'foo',
                    'password' => 'bar',
                    'dsn'      => 'sqlite::memory:',
                    'options'  => [
                        PDO::ATTR_EMULATE_PREPARES => true,
                    ]
                ],
            ],
        ]);
        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    protected function setUp()
    {
        $this->factory  = new PdoAdapterFactory();
        $this->services = $services = new ServiceManager();

        $this->setApplicationConfig([
            'modules' => [
                'Laminas\ApiTools\OAuth2',
            ],
            'module_listener_options' => [
                'module_paths' => [__DIR__ . '/../../'],
                'config_glob_paths' => [],
            ],
            'service_listener_options' => [],
            'service_manager' => [],
        ]);
        parent::setUp();
    }
}
