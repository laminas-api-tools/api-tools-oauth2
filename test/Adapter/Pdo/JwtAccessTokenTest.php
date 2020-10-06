<?php

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

use DateTime;

/**
 * @todo This adapter logic is not supported in the PDO adapter.
 */
class JwtAccessTokenTest extends AbstractBaseTest
{
    /**
     * @dataProvider provideStorage
     * @psalm-return never
     */
    public function testJwtWithJti(object $storage)
    {
        $this->markTestIncomplete('JWT access token is not yet supported in PDO adapter');

        $expires = new DateTime('today +1 day');
        $expires = $expires->format('U');

        $clientId = 'oauth_test_client';
        $subject  = 'jtisubject';
        $audience = 'http://unittest';
        $jti      = 'jti';

        $this->assertTrue($storage->setJti($clientId, $subject, $audience, $expires, $jti));

        $storage->getJti($clientId, $subject, $audience, $expires, $jti);
        $this->assertFalse($storage->getJti($clientId, $subject, $audience, $expires, 'invlalid'));
    }
}
