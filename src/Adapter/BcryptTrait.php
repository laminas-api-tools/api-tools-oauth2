<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Adapter;

use Laminas\Crypt\Password\Bcrypt;

/**
 * Trait BcryptTrait
 *
 * @package Laminas\ApiTools\OAuth2\Adapter
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
trait BcryptTrait
{
    /**
     * @var int
     */
    protected $bcryptCost = 10;

    /**
     * @var Bcrypt
     */
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
     * @param $value
     * @return $this
     */
    public function setBcryptCost($value)
    {
        $this->bcryptCost = (int) $value;
        return $this;
    }

    /**
     * Check password using bcrypt
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return $this->verifyHash($password, $user['password']);
    }

    /**
     * @param $string
     */
    protected function createBcryptHash(&$string)
    {
        $string = $this->getBcrypt()->create($string);
    }

    /**
     * Check hash using bcrypt
     *
     * @param $hash
     * @param $check
     * @return bool
     */
    protected function verifyHash($check, $hash)
    {
        return $this->getBcrypt()->verify($check, $hash);
    }
}
