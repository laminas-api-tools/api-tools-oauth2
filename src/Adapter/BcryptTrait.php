<?php

namespace Laminas\ApiTools\OAuth2\Adapter;

use Laminas\Crypt\Password\Bcrypt;

/**
 * Trait BcryptTrait
 */
trait BcryptTrait
{
    /** @var int */
    protected $bcryptCost = 10;

    /** @var Bcrypt */
    protected $bcrypt;

    /**
     * @return Bcrypt
     */
    public function getBcrypt()
    {
        if (null === $this->bcrypt) {
            $this->bcrypt = new Bcrypt();
            $this->bcrypt->setCost($this->bcryptCost);
        }

        return $this->bcrypt;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setBcryptCost($value)
    {
        $this->bcryptCost = (int) $value;
        return $this;
    }

    /**
     * Check password using bcrypt
     */
    protected function checkPassword(string $user, string $password): bool
    {
        return $this->verifyHash($password, $user['password']);
    }

    /**
     * @param string $string Passed by reference
     */
    protected function createBcryptHash(&$string)
    {
        $string = $this->getBcrypt()->create($string);
    }

    /**
     * Check hash using bcrypt
     */
    protected function verifyHash(string $check, string $hash): bool
    {
        return $this->getBcrypt()->verify($check, $hash);
    }
}
