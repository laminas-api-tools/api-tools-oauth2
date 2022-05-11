<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\OAuth2\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\ApiTools\OAuth2\Controller\AuthController;
use Laminas\ApiTools\OAuth2\Provider\UserId\UserIdProviderInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\Pdo\Pdo as PdoDriver;
use Laminas\Db\Sql\Sql;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Stdlib\Parameters;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OAuth2\Request as OAuth2Request;
use OAuth2\Server as OAuth2Server;
use PDO;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionException;
use ReflectionProperty;

use function array_key_exists;
use function file_get_contents;
use function json_decode;
use function preg_match;

class AuthControllerTest extends AbstractHttpControllerTestCase
{
    use ProphecyTrait;

    /** @var Adapter|PDO|null */
    protected $db;

    protected function setUp(): void
    {
        $this->setApplicationConfig(include __DIR__ . '/../TestAsset/pdo.application.config.php');
        parent::setUp();
        $this->setupDb();
    }

    public function setupDb(): void
    {
        $pdo = $this->getApplication()->getServiceManager()->get(PdoAdapter::class);
        $r   = new ReflectionProperty($pdo, 'db');
        $r->setAccessible(true);
        $db = $r->getValue($pdo);

        $sql = file_get_contents(__DIR__ . '/../TestAsset/database/pdo.sql');
        $db->exec($sql);
    }

    /**
     * @return Adapter|PDO
     * @throws ReflectionException
     */
    public function getDb()
    {
        if ($this->db) {
            return $this->db;
        }

        $adapter = $this->getApplication()->getServiceManager()->get(PdoAdapter::class);
        $r       = new ReflectionProperty($adapter, 'db');
        $r->setAccessible(true);
        $this->db = new Adapter(new PdoDriver($r->getValue($adapter)));
        return $this->db;
    }

    public function setRequest(AuthController $controller, Request $request): void
    {
        $r = new ReflectionProperty($controller, 'request');
        $r->setAccessible(true);
        $r->setValue($controller, $request);
    }

    public function setBodyParamsPlugin(AuthController $controller): void
    {
        $plugins = $controller->getPluginManager();
        $plugins->setService('bodyParams', new TestAsset\BodyParams());
    }

    /** @param mixed $value */
    public function setParamsPlugin(AuthController $controller, string $key, $value): void
    {
        $params = $this->prophesize(Params::class);
        $params->__invoke($key)->willReturn($value);
        $params->setController($controller)->shouldBeCalled();

        $plugins = $controller->getPluginManager();
        $plugins->setService('params', $params->reveal());
    }

    public function testToken(): void
    {
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'client_credentials');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(! empty($response['access_token']));
        $this->assertTrue(! empty($response['expires_in']));
        $this->assertTrue(array_key_exists('scope', $response));
        $this->assertTrue(! empty($response['token_type']));
    }

    public function testTokenErrorIsApiProblem(): void
    {
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'fake_grant_type');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('unsupported_grant_type', $response['title']);
        $this->assertEquals('Grant type "fake_grant_type" not supported', $response['detail']);
        $this->assertEquals('400', $response['status']);
    }

    public function testTokenErrorIsOAuth2Format(): void
    {
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'fake_grant_type');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->setIsOAuth2FormatResponse();

        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('unsupported_grant_type', $response['error']);
        $this->assertEquals('Grant type "fake_grant_type" not supported', $response['error_description']);
    }

    public function testTokenRevoke(): void
    {
        $request = $this->getRequest();
        $request->getPost()->set('token', '00bdec1ee9ee80762f39e5340495a31a203cd460');
        $request->getPost()->set('token_type_hint', 'access_token');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth/revoke');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('revoke');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(! empty($response['revoked']));
        $this->assertTrue($response['revoked']);
    }

    public function testTokenRevokeWithoutTokenIsError(): void
    {
        $request = $this->getRequest();
        $request->getPost()->set('token_type_hint', 'access_token');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth/revoke');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('invalid_request', $response['title']);
        $this->assertEquals('Missing token parameter to revoke', $response['detail']);
        $this->assertEquals('400', $response['status']);
    }

    public function testAuthorizeForm(): void
    {
        $request = $this->getRequest();
        $request->getHeaders()->addHeaderLine('Accept', 'text/html');

        $this->dispatch('/oauth/authorize', 'GET', [
            'response_type' => 'code',
            'client_id'     => 'testclient',
            'state'         => 'xyz',
        ]);
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(200);
        $this->assertXpathQuery('//form/input[@name="authorized" and @value="yes"]');
        $this->assertXpathQuery('//form/input[@name="authorized" and @value="no"]');
    }

    public function testAuthorizeParamErrorIsApiProblem(): void
    {
        $this->dispatch('/oauth/authorize');

        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['title']);
        $this->assertEquals('No client id supplied', $response['detail']);
        $this->assertEquals('400', $response['status']);
    }

    public function testAuthorizeParamErrorIsOAuth2Format(): void
    {
        $this->setIsOAuth2FormatResponse();

        $this->dispatch('/oauth/authorize');

        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');
        $this->assertResponseStatusCode(400);

        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals('application/json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('invalid_client', $response['error']);
        $this->assertEquals('No client id supplied', $response['error_description']);
    }

    public function testAuthorizeCode(): void
    {
        $request = $this->getRequest();
        $request->setQuery(new Parameters([
            'response_type' => 'code',
            'client_id'     => 'testclient',
            'state'         => 'xyz',
            'redirect_uri'  => '/oauth/receivecode',
        ]));
        $request->setPost(new Parameters([
            'authorized' => 'yes',
        ]));
        $request->setMethod('POST');

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();
        if (preg_match('#code=([0-9a-f]+)#', $location, $matches)) {
            $code = $matches[1];
        }

        // test data in database is correct
        $adapter = $this->getDb();
        $sql     = new Sql($adapter);
        $select  = $sql->select();
        $select->from('oauth_authorization_codes');
        $select->where(['authorization_code' => $code]);

        $selectString = $sql->getSqlStringForSqlObject($select);
        $results      = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
        $this->assertEquals(null, $results[0]['user_id']);

        // test get token from authorized code
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'authorization_code');
        $request->getPost()->set('code', $code);
        $request->getPost()->set('redirect_uri', '/oauth/receivecode');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');

        $this->getApplication()->bootstrap();
        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(! empty($response['access_token']));
    }

    public function testImplicitClientAuth(): void
    {
        $config      = $this->getApplication()->getConfig();
        $oauthConfig = $config['api-tools-oauth2'];

        $allowImplicit = $oauthConfig['allow_implicit'] ?? false;

        if (! $allowImplicit) {
            $this->markTestSkipped('The allow implicit client mode is disabled');
        }

        $request = $this->getRequest();
        $request->getQuery()->set('response_type', 'token');
        $request->getQuery()->set('client_id', 'testclient');
        $request->getQuery()->set('state', 'xyz');
        $request->getQuery()->set('redirect_uri', '/oauth/receivecode');
        $request->getPost()->set('authorized', 'yes');
        $request->setMethod('POST');

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect());
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $token    = '';
        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();

        if (preg_match('#access_token=([0-9a-f]+)#', $location, $matches)) {
            $token = $matches[1];
        }
        $this->assertTrue(! empty($token));
    }

    public function testResource(): void
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'client_credentials');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue(! empty($response['access_token']));

        $token = $response['access_token'];

        // test resource through token by POST
        $post = $request->getPost();
        unset($post['grant_type']);
        $post->set('access_token', $token);
        $server = $request->getServer();
        unset($server['PHP_AUTH_USER']);
        unset($server['PHP_AUTH_PW']);

        $this->getApplication()->bootstrap();
        $this->dispatch('/oauth/resource');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('resource');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);

        $this->assertTrue($response['success']);
        $this->assertEquals('You accessed my APIs!', $response['message']);

        // test resource through token by Bearer header
        $request->getHeaders()
            ->addHeaderLine('Authorization', 'Bearer ' . $token);
        unset($post['access_token']);
        $request->setMethod('GET');

        $this->getApplication()->bootstrap();
        $this->dispatch('/oauth/resource');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('resource');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('You accessed my APIs!', $response['message']);
    }

    protected function setIsOAuth2FormatResponse(): void
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $config = $serviceManager->get('config');
        $config['api-tools-oauth2']['api_problem_error_response'] = false;

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('config', $config);
    }

    public function testTokenActionUsesCodeFromTokenExceptionIfPresentToCreateApiProblem(): void
    {
        $exception          = new TestAsset\CustomProblemDetailsException('problem', 409);
        $exception->type    = 'custom';
        $exception->title   = 'title';
        $exception->details = ['some' => 'details'];

        $oauth2Server = $this->prophesize(OAuth2Server::class);
        $oauth2Server
            ->handleTokenRequest(Argument::type(OAuth2Request::class))
            ->willThrow($exception);
        $factory = function () use ($oauth2Server): OAuth2Server {
            return $oauth2Server->reveal();
        };

        $provider = $this->prophesize(UserIdProviderInterface::class)->reveal();

        $controller = new AuthController($factory, $provider);
        $this->setBodyParamsPlugin($controller);
        $this->setParamsPlugin($controller, 'oauth', []);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->setRequest($controller, $request);

        $result = $controller->tokenAction();
        $this->assertInstanceOf(ApiProblemResponse::class, $result);
        $problem = $result->getApiProblem();
        $this->assertEquals(409, $problem->status);
        $this->assertEquals('custom', $problem->type);
        $this->assertEquals('title', $problem->title);
        $this->assertEquals('details', $problem->some);
    }

    public function testTokenActionUses401CodeIfTokenExceptionCodeIsInvalidWhenCreatingApiProblem(): void
    {
        $exception          = new TestAsset\CustomProblemDetailsException('problem', 601);
        $exception->type    = 'custom';
        $exception->title   = 'title';
        $exception->details = ['some' => 'details'];

        $oauth2Server = $this->prophesize(OAuth2Server::class);
        $oauth2Server
            ->handleTokenRequest(Argument::type(OAuth2Request::class))
            ->willThrow($exception);
        $factory = function () use ($oauth2Server): OAuth2Server {
            return $oauth2Server->reveal();
        };

        $provider = $this->prophesize(UserIdProviderInterface::class)->reveal();

        $controller = new AuthController($factory, $provider);
        $this->setBodyParamsPlugin($controller);
        $this->setParamsPlugin($controller, 'oauth', []);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $this->setRequest($controller, $request);

        $result = $controller->tokenAction();
        $this->assertInstanceOf(ApiProblemResponse::class, $result);
        $problem = $result->getApiProblem();
        $this->assertEquals(401, $problem->status);
        $this->assertEquals('custom', $problem->type);
        $this->assertEquals('title', $problem->title);
        $this->assertEquals('details', $problem->some);
    }
}
