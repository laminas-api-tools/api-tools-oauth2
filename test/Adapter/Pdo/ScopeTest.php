<?php

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

use OAuth2\Scope;
use OAuth2\Storage\NullStorage;
use OAuth2\Storage\ScopeInterface;

use function explode;
use function sort;

class ScopeTest extends BaseTest
{
    /** @dataProvider provideStorage */
    public function testScopeExists(object $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        if (! $storage instanceof ScopeInterface) {
            // incompatible storage
            return;
        }

        // Test getting scopes
        $scopeUtil = new Scope($storage);
        $this->assertTrue($scopeUtil->scopeExists('supportedscope1'));
        $this->assertTrue($scopeUtil->scopeExists('supportedscope1 supportedscope2 supportedscope3'));
        $this->assertFalse($scopeUtil->scopeExists('fakescope'));
        $this->assertFalse($scopeUtil->scopeExists('supportedscope1 supportedscope2 supportedscope3 fakescope'));
    }

    /** @dataProvider provideStorage */
    public function testGetDefaultScope(object $storage)
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        if (! $storage instanceof ScopeInterface) {
            // incompatible storage
            return;
        }

        // Test getting default scope
        $scopeUtil = new Scope($storage);
        $expected  = explode(' ', $scopeUtil->getDefaultScope());
        $actual    = explode(' ', 'defaultscope1 defaultscope2');
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
}
