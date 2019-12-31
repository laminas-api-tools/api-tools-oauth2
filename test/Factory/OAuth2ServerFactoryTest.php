<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Factory\OAuth2ServerFactory;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;

class OAuth2ServerFactoryTest extends AbstractHttpControllerTestCase
{

    /**
     * @var OAuth2ServerFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @expectedException \Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException
     */
    public function testExceptionThrownOnMissingStorageClass()
    {
        $this->services->setService('Config', array());
        $this->factory->createService($this->services);
    }

    public function testServiceCreatedWithDefaults()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();

        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'api-tools-oauth2' => array(
                'storage' => 'TestAdapter'
            )
        ));

        $expectedService = new \OAuth2\Server($adapter, array('enforce_state' => true, 'allow_implicit' => false, 'access_lifetime' => 3600));
        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('OAuth2\Server', $service);
        $this->assertEquals($expectedService, $service);
    }

    public function testServiceCreatedWithOverriddenValues()
    {
        $adapter = $this->getMockBuilder('OAuth2\Storage\Pdo')->disableOriginalConstructor()->getMock();

        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('Config', array(
            'api-tools-oauth2' => array(
                'storage'        => 'TestAdapter',
                'enforce_state'  => false,
                'allow_implicit' => true,
                'access_lifetime' => 12000,
            )
        ));

        $expectedService = new \OAuth2\Server($adapter, array('enforce_state' => false, 'allow_implicit' => true, 'access_lifetime' => 12000));
        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf('OAuth2\Server', $service);
        $this->assertEquals($expectedService, $service);
    }

    protected function setUp()
    {
        $this->factory = new OAuth2ServerFactory();

        $this->services = $services = new ServiceManager();

    }
}
