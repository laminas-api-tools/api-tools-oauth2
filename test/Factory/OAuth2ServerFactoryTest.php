<?php

namespace LaminasTest\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException;
use Laminas\ApiTools\OAuth2\Factory\OAuth2ServerFactory;
use Laminas\ApiTools\OAuth2\Factory\OAuth2ServerInstanceFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\JwtBearer;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\ClientInterface;
use OAuth2\Storage\JwtBearerInterface;
use OAuth2\Storage\Pdo;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\UserCredentialsInterface;

use function constant;
use function defined;
use function version_compare;

class OAuth2ServerFactoryTest extends AbstractHttpControllerTestCase
{
    /** @var OAuth2ServerFactory */
    protected $factory;

    /** @var ServiceManager */
    protected $services;

    protected function setUp(): void
    {
        $this->factory  = new OAuth2ServerFactory();
        $this->services = $services = new ServiceManager();

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

    public function testExceptionThrownOnMissingStorageClass()
    {
        $this->expectException(RuntimeException::class);

        $this->services->setService('config', []);
        $smFactory = $this->factory;
        $factory   = $smFactory($this->services, 'OAuth2Server');

        $this->expectException(RuntimeException::class);
        $factory();
    }

    public function testServiceCreatedWithDefaults()
    {
        $adapter = $this->getMockBuilder(Pdo::class)->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'storage'     => 'TestAdapter',
                'grant_types' => [
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ],
            ],
        ]);

        $expectedService = new Server(
            $adapter,
            [
                'enforce_state'   => true,
                'allow_implicit'  => false,
                'access_lifetime' => 3600,
            ]
        );

        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $expectedService->addGrantType(new JwtBearer($adapter, ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf(OAuth2ServerInstanceFactory::class, $service);
        $server = $service();
        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithOverriddenValues()
    {
        $adapter = $this->getMockBuilder(Pdo::class)->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'storage'         => 'TestAdapter',
                'enforce_state'   => false,
                'allow_implicit'  => true,
                'access_lifetime' => 12000,
                'grant_types'     => [
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ],
            ],
        ]);

        $expectedService = new Server(
            $adapter,
            [
                'enforce_state'   => false,
                'allow_implicit'  => true,
                'access_lifetime' => 12000,
            ]
        );

        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $expectedService->addGrantType(new JwtBearer($adapter, ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf(OAuth2ServerInstanceFactory::class, $service);
        $server = $service();
        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithOverriddenValuesInOptionsSubArray()
    {
        $adapter = $this->getMockBuilder(Pdo::class)->disableOriginalConstructor()->getMock();

        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'storage'     => 'TestAdapter',
                'options'     => [
                    'enforce_state'   => false,
                    'allow_implicit'  => true,
                    'access_lifetime' => 12000,
                ],
                'grant_types' => [
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ],
            ],
        ]);

        $expectedService = new Server(
            $adapter,
            [
                'enforce_state'   => false,
                'allow_implicit'  => true,
                'access_lifetime' => 12000,
            ]
        );

        $expectedService->addGrantType(new ClientCredentials($adapter));
        $expectedService->addGrantType(new AuthorizationCode($adapter));
        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $expectedService->addGrantType(new JwtBearer($adapter, ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf(OAuth2ServerInstanceFactory::class, $service);
        $server = $service();
        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithStoragesAsArray()
    {
        if (defined('HHVM_VERSION') && version_compare(constant('HHVM_VERSION'), '3.8', 'lt')) {
            $this->markTestSkipped('Skipping test until we have HHVM 3.8 support');
        }

        $storage = [
            'access_token'       => $this->getMockForAbstractClass(AccessTokenInterface::class),
            'authorization_code' => $this->getMockForAbstractClass(AuthorizationCodeInterface::class),
            'client_credentials' => $this->getMockForAbstractClass(ClientCredentialsInterface::class),
            'client'             => $this->getMockForAbstractClass(ClientInterface::class),
            'refresh_token'      => $this->getMockForAbstractClass(RefreshTokenInterface::class),
            'user_credentials'   => $this->getMockForAbstractClass(UserCredentialsInterface::class),
            'public_key'         => $this->getMockForAbstractClass(PublicKeyInterface::class),
            'jwt_bearer'         => $this->getMockForAbstractClass(JwtBearerInterface::class),
            'scope'              => $this->getMockForAbstractClass(ScopeInterface::class),
        ];

        $this->services->setService('OAuth2\Storage\AccessToken', $storage['access_token']);
        $this->services->setService('OAuth2\Storage\AuthorizationCode', $storage['authorization_code']);
        $this->services->setService('OAuth2\Storage\ClientCredentials', $storage['client_credentials']);
        $this->services->setService('OAuth2\Storage\Client', $storage['client']);
        $this->services->setService('OAuth2\Storage\RefreshToken', $storage['refresh_token']);
        $this->services->setService('OAuth2\Storage\UserCredentials', $storage['user_credentials']);
        $this->services->setService('OAuth2\Storage\PublicKey', $storage['public_key']);
        $this->services->setService('OAuth2\Storage\JWTBearer', $storage['jwt_bearer']);
        $this->services->setService('OAuth2\Storage\Scope', $storage['scope']);

        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'storage'     => [
                    'access_token'       => 'OAuth2\Storage\AccessToken',
                    'authorization_code' => 'OAuth2\Storage\AuthorizationCode',
                    'client_credentials' => 'OAuth2\Storage\ClientCredentials',
                    'client'             => 'OAuth2\Storage\Client',
                    'refresh_token'      => 'OAuth2\Storage\RefreshToken',
                    'user_credentials'   => 'OAuth2\Storage\UserCredentials',
                    'public_key'         => 'OAuth2\Storage\PublicKey',
                    'jwt_bearer'         => 'OAuth2\Storage\JWTBearer',
                    'scope'              => 'OAuth2\Storage\Scope',
                ],
                'grant_types' => [
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ],
            ],
        ]);

        $expectedService = new Server(
            $storage,
            [
                'enforce_state'   => true,
                'allow_implicit'  => false,
                'access_lifetime' => 3600,
            ]
        );

        $expectedService->addGrantType(new ClientCredentials($storage['client_credentials']));
        $expectedService->addGrantType(new AuthorizationCode($storage['authorization_code']));
        $expectedService->addGrantType(new UserCredentials($storage['user_credentials']));
        $expectedService->addGrantType(new RefreshToken($storage['refresh_token']));
        $expectedService->addGrantType(new JwtBearer($storage['jwt_bearer'], ''));

        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf(OAuth2ServerInstanceFactory::class, $service);
        $server = $service();
        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testServiceCreatedWithSelectedGrandTypes()
    {
        $adapter = $this->getMockBuilder(Pdo::class)->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'storage'     => 'TestAdapter',
                'grant_types' => [
                    'client_credentials' => false,
                    'password'           => true,
                    'refresh_token'      => true,
                ],
            ],
        ]);

        $expectedService = new Server(
            $adapter,
            [
                'enforce_state'   => true,
                'allow_implicit'  => false,
                'access_lifetime' => 3600,
            ]
        );

        $expectedService->addGrantType(new UserCredentials($adapter));
        $expectedService->addGrantType(new RefreshToken($adapter));
        $service = $this->factory->createService($this->services);
        $this->assertInstanceOf(OAuth2ServerInstanceFactory::class, $service);
        $server = $service();
        $this->assertInstanceOf(Server::class, $server);
        $this->assertEquals($expectedService, $server);
    }

    public function testSubsequentCallsReturnTheSameInstance()
    {
        $adapter = $this->getMockBuilder(Pdo::class)->disableOriginalConstructor()->getMock();
        $this->services->setService('TestAdapter', $adapter);
        $this->services->setService('config', [
            'api-tools-oauth2' => [
                'storage'     => 'TestAdapter',
                'grant_types' => [
                    'client_credentials' => true,
                    'authorization_code' => true,
                    'password'           => true,
                    'refresh_token'      => true,
                    'jwt'                => true,
                ],
            ],
        ]);

        $factory = $this->factory->createService($this->services);
        $server  = $factory();
        $this->assertSame($server, $factory());
    }
}
