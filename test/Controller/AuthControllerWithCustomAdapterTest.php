<?php

namespace LaminasTest\ApiTools\OAuth2\Controller;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use function json_decode;

class AuthControllerWithCustomAdapterTest extends AbstractHttpControllerTestCase
{
    protected function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../TestAsset/custom.application.config.php');

        parent::setUp();
    }

    public function testToken()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $request->getPost()->set('grant_type', 'password');
        $request->getPost()->set('client_id', 'public');
        $request->getPost()->set('username', 'banned_user');
        $request->getPost()->set('password', 'testpass');
        $request->setMethod('POST');

        $this->dispatch('/oauth');
        $this->assertControllerName('Laminas\ApiTools\OAuth2\Controller\Auth');
        $this->assertActionName('token');
        $this->assertResponseStatusCode(401);

        /** @var Response $response */
        $response = $this->getResponse();
        $headers  = $response->getHeaders();
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());

        $response = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals('banned', $response['title']);
        $this->assertEquals('User is banned', $response['detail']);
        $this->assertEquals('401', $response['status']);
    }
}
