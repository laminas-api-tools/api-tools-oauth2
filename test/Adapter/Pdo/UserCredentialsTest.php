<?php

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

use OAuth2\Storage\NullStorage;
use OAuth2\Storage\UserCredentialsInterface;

use function is_array;

class UserCredentialsTest extends AbstractBaseTest
{
    /** @dataProvider provideStorage */
    public function testCheckUserCredentials(UserCredentialsInterface $storage): void
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // correct credentials
        $this->assertTrue($storage->checkUserCredentials('oauth_test_user', 'testpass'));
        // invalid password
        $this->assertFalse($storage->checkUserCredentials('oauth_test_user', 'fakepass'));
        // invalid username
        $this->assertFalse($storage->checkUserCredentials('fakeusername', 'testpass'));

        // invalid username
        $this->assertFalse($storage->getUserDetails('fakeusername'));

        // ensure all properties are set
        $user = $storage->getUserDetails('oauth_test_user');
        $this->assertTrue($user !== false);
        $this->assertArrayHasKey('user_id', $user);
        $this->assertEquals($user['user_id'], 'oauth_test_user');
    }

    /**
     * @dataProvider provideStorage
     * @todo Support OpenID, and provide validation via testing.
     * @psalm-return never
     */
    public function testUserClaims(UserCredentialsInterface $storage)
    {
        $this->markTestIncomplete('OpenID support is not yet implemented');

        $claims = $storage->getUserClaims('oauth_test_user', 'profile');
        $this->assertTrue(is_array($claims));

        $claims = $storage->getUserClaims('oauth_test_user', 'email');
        $this->assertTrue(is_array($claims));

        $claims = $storage->getUserClaims('oauth_test_user', 'address');
        $this->assertTrue(is_array($claims));

        $claims = $storage->getUserClaims('oauth_test_user', 'phone');
        $this->assertTrue(is_array($claims));

        $this->assertFalse($storage->getUserClaims('oauth_test_user', 'invalid'));
        $this->assertFalse($claims = $storage->getUserClaims('invalid', 'invalid'));
    }
}
