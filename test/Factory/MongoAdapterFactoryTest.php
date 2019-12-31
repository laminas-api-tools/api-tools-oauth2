<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Factory\MongoAdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ReflectionObject;

class MongoAdapterFactoryTest extends AbstractHttpControllerTestCase
{
    /**
     * @var MongoAdapterFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    protected function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('The Mongo extension is not available.');
        }

        $this->factory  = new MongoAdapterFactory();
        $this->services = $services = new ServiceManager();
    }

    /**
     * @expectedException \Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownWhenMissingMongoCredentials()
    {
        $this->services->setService('Config', array());
        $adapter = $this->factory->createService($this->services);

        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    public function testInstanceCreated()
    {
        $this->services->setService('Config', array(
            'api-tools-oauth2' => array(
                'mongo' => array(
                    'database' => 'test',
                    'dsn'      => 'mongodb://127.0.0.1:27017'
                )
            )
        ));

        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\MongoAdapter', $adapter);
    }

    public function testInstanceCreatedWithMongoDbInServiceLocator()
    {
        $this->services->setService('Config', array(
            'api-tools-oauth2' => array(
                'mongo' => array(
                    'locator_name' => 'testdb',
                ),
            ),
        ));
        $mock = $this->getMock('\MongoDB', array(), array(), '', false);
        $this->services->setService('testdb', $mock);

        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\MongoAdapter', $adapter);
    }

    public function testCanPassAdapterConfigurationWhenCreatingInstance()
    {
        $this->services->setService('Config', array(
            'api-tools-oauth2' => array(
                'mongo' => array(
                    'locator_name' => 'testdb',
                ),
                'storage_settings' => array(
                    'user_table' => 'my_users',
                ),
            ),
        ));
        $mock = $this->getMock('\MongoDB', array(), array(), '', false);
        $this->services->setService('testdb', $mock);

        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\MongoAdapter', $adapter);

        $r = new ReflectionObject($adapter);
        $c = $r->getProperty('config');
        $c->setAccessible(true);
        $config = $c->getValue($adapter);
        $this->assertEquals('my_users', $config['user_table']);
    }
}
