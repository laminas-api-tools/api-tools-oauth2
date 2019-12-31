<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\OAuth2\Controller;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as M;
use Mockery\Loader;
use PDO;

class AuthControllerWithLaminasAuthenticationServiceTest extends AbstractHttpControllerTestCase
{
    protected $loader;
    protected $db;

    public function setUp()
    {
        copy(
            __DIR__ . '/../TestAsset/autoload_laminas_authenticationservice/db_oauth2.sqlite',
            sys_get_temp_dir() . '/dbtest.sqlite'
        );

        $this->setApplicationConfig(
            include __DIR__ . '/../TestAsset/laminas.authenticationservice.application.config.php'
        );

        $this->loader = new Loader;
        $this->loader->register();

        parent::setUp();
    }

    public function getDb()
    {
        $config = $this->getApplication()->getServiceManager()->get('Config');
        return new PDO($config['api-tools-oauth2']['db']['dsn']);
    }

    public function tearDown()
    {
        $db = sys_get_temp_dir() . '/dbtest.sqlite';
        if (file_exists($db)) {
            unlink($db);
        }
    }

    public function getAuthenticationService()
    {
        $storage = M::mock('Laminas\Authentication\Storage\StorageInterface');
        $storage->shouldReceive('isEmpty')->once()->andReturn(false);
        $storage->shouldReceive('read')->once()->andReturn(123);

        $authentication = $this->getApplication()->
            getServiceManager()->get('Laminas\Authentication\AuthenticationService');

        $authentication->setStorage($storage);

        return $authentication;
    }

    public function testAuthorizeCode()
    {
        $_GET['response_type'] = 'code';
        $_GET['client_id'] = 'testclient';
        $_GET['state'] = 'xyz';
        $_GET['redirect_uri'] = '/oauth/receivecode';
        $_POST['authorized'] = 'yes';
        $_SERVER['REQUEST_METHOD'] = 'POST';

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
        $row = $this->getDb()
            ->query($query)
            ->fetch();

        $this->assertEquals('123', $row['user_id']);

        // test get token from authorized code
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'authorization_code');
        $request->getPost()->set('code', $code);
        $request->getPost()->set('redirect_uri', '/oauth/receivecode');
        $request->getServer()->set('PHP_AUTH_USER', 'testclient');
        $request->getServer()->set('PHP_AUTH_PW', 'testpass');

        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(200);

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertNotEmpty($response['access_token']);
    }
}
