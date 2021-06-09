<?php

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

/**
 * @todo This adapter logic is not supported in the PDO adapter
 */
class JwtBearerTest extends AbstractBaseTest
{
    /** @dataProvider provideStorage */
    public function testGetClientKey(object $storage)
    {
        $this->markTestIncomplete('JWT is not supported in the PDO adapter');

        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // nonexistant client_id
        $key = $storage->getClientKey('this-is-not-real', 'nor-is-this');
        $this->assertFalse($key);

        // valid client_id invalid subject
        $key = $storage->getClientKey('oauth_test_client', 'nor-is-this');
        $this->assertFalse($key);

        // valid client_id and subject
        $key = $storage->getClientKey('oauth_test_client', 'test_subject');
        $this->assertNotNull($key);
        $this->assertEquals(
            $key,
            <<<'END'
                -----BEGIN PUBLIC KEY-----
                MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCvfF+Cw8nzsc9Twam37SYpAW3+
                lRGUle/hYnd9obfBvDHKBvgb1WfGCblwjwImGL9u0rEIW2sspkwBEsGGFFBmSaqq
                fvEER7Yr++VIidOUHkas3cHO1TVoERO3s0THOobw0OzghPnMJL6ayelYOESwfnqR
                WfuEMSaWaW0G38QPzwIDAQAB
                -----END PUBLIC KEY-----
                END
        );
    }
}
