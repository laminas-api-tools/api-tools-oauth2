<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Factory\PdoAdapterFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

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
        $this->services->setService('Config', array());
        $adapter = $this->factory->createService($this->services);

        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    public function testInstanceCreated()
    {
        $this->services->setService('Config', array(
            'api-tools-oauth2' => array(
                'db' => array(
                    'username' => 'foo',
                    'password' => 'bar',
                    'dsn'      => 'sqlite::memory:'
                )
            )
        ));
        $adapter = $this->factory->createService($this->services);
        $this->assertInstanceOf('Laminas\ApiTools\OAuth2\Adapter\PdoAdapter', $adapter);
    }

    protected function setUp()
    {
        $this->factory  = new PdoAdapterFactory();
        $this->services = $services = new ServiceManager();
    }
}
