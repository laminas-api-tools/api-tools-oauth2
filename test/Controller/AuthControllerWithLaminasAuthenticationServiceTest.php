<?php

namespace LaminasTest\ApiTools\OAuth2\Controller;

use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\StorageInterface;
use Laminas\Stdlib\Parameters;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as M;
use PDO;
use ReflectionProperty;

use function file_get_contents;
use function json_decode;
use function preg_match;
use function sprintf;
use function var_export;

class AuthControllerWithLaminasAuthenticationServiceTest extends AbstractHttpControllerTestCase
{
    /** @var PDO */
    protected $db;

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/laminas.authenticationservice.application.config.php'
        );

        parent::setUp();
        $this->setupDb();
    }

    public function setupDb()
    {
        $pdo = $this->getApplication()->getServiceManager()->get(PdoAdapter::class);
        $r   = new ReflectionProperty($pdo, 'db');
        $r->setAccessible(true);
        $db = $r->getValue($pdo);

        $sql = file_get_contents(__DIR__ . '/../TestAsset/database/db_oauth2.sql');
        $db->exec($sql);
        $this->db = $db;
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    public function getAuthenticationService(): AuthenticationService
    {
        $storage = M::mock(StorageInterface::class);
        $storage->shouldReceive('isEmpty')->once()->andReturn(false);
        $storage->shouldReceive('read')->once()->andReturn(123);

        $authentication = $this->getApplication()
            ->getServiceManager()
            ->get(AuthenticationService::class);

        $authentication->setStorage($storage);

        return $authentication;
    }

    public function testAuthorizeCode()
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

        $this->getAuthenticationService();

        $this->dispatch('/oauth/authorize');
        $this->assertTrue($this->getResponse()->isRedirect(), var_export($this->getResponse(), 1));
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('authorize');

        $location = $this->getResponse()->getHeaders()->get('Location')->getUri();
        if (preg_match('#code=([0-9a-f]+)#', $location, $matches)) {
            $code = $matches[1];
        }

        // test data in database is correct
        $query = sprintf(
            'SELECT * FROM oauth_authorization_codes WHERE authorization_code = \'%s\'',
            $code
        );
        $row   = $this->getDb()
            ->query($query)
            ->fetch();

        $this->assertEquals(null, $row['user_id']);

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
        $this->assertNotEmpty($response['access_token']);
    }
}
