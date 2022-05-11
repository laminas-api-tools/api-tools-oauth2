<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Adapter;

use Laminas\Crypt\Password\Bcrypt;
use MongoClient;
use OAuth2\Storage\Mongo as OAuth2Mongo;

use function class_exists;
use function extension_loaded;
use function func_num_args;
use function version_compare;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class MongoAdapter extends OAuth2Mongo
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
     *
     * @param array<string, string> $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password)
    {
        return $this->verifyHash($password, $user['password']);
    }

    /**
     * @param string $string Passed by reference
     */
    protected function createBcryptHash(&$string): void
    {
        $string = $this->getBcrypt()->create($string);
    }

    /**
     * Check hash using bcrypt
     *
     * @param string $hash
     * @param string $check
     * @return bool
     */
    protected function verifyHash($check, $hash)
    {
        return $this->getBcrypt()->verify($check, $hash);
    }

    /**
     * @param MongoDB|array<string, string|int> $connection
     * @param array $config
     * @throws Exception\RuntimeException
     */
    public function __construct($connection, $config = [])
    {
        // phpcs:disable
        if (
            ! (extension_loaded('mongodb') || extension_loaded('mongo'))
            || ! class_exists(MongoClient::class)
            || version_compare(MongoClient::VERSION, '1.4.1', '<')
        ) {
            throw new Exception\RuntimeException(
                'The MongoAdapter requires either the Mongo Driver v1.4.1 or '
                . 'ext/mongodb + the alcaeus/mongo-php-adapter package (which provides '
                . 'backwards compatibility for ext/mongo classes)'
            );
        }
        // phpcs:enable

        parent::__construct($connection, $config);
    }

    /**
     * Check client credentials
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return bool
     */
    public function checkClientCredentials($clientId, $clientSecret = null)
    {
        if ($result = $this->collection('client_table')->findOne(['client_id' => $clientId])) {
            return $this->verifyHash($clientSecret, $result['client_secret']);
        }

        return false;
    }

    /**
     * Set client details
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $grantTypes
     * @param string $scopeOrUserId If 5 arguments, userId; if 6, scope.
     * @param string $userId
     * @return bool
     */
    public function setClientDetails(
        $clientId,
        $clientSecret = null,
        $redirectUri = null,
        $grantTypes = null,
        $scopeOrUserId = null,
        $userId = null
    ) {
        if (func_num_args() > 5) {
            $scope = $scopeOrUserId;
        } else {
            $userId = $scopeOrUserId;
            $scope  = null;
        }

        if (! empty($clientSecret)) {
            $this->createBcryptHash($clientSecret);
        }

        if ($this->getClientDetails($clientId)) {
            $this->collection('client_table')->update(
                ['client_id' => $clientId],
                [
                    '$set' => [
                        'client_secret' => $clientSecret,
                        'redirect_uri'  => $redirectUri,
                        'grant_types'   => $grantTypes,
                        'scope'         => $scope,
                        'user_id'       => $userId,
                    ],
                ]
            );
        } else {
            $this->collection('client_table')->insert(
                [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri'  => $redirectUri,
                    'grant_types'   => $grantTypes,
                    'scope'         => $scope,
                    'user_id'       => $userId,
                ]
            );
        }

        return true;
    }

    /**
     * Set the user
     *
     * @param string $username
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @return bool
     */
    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        $this->createBcryptHash($password);

        if ($this->getUser($username)) {
            $this->collection('user_table')->update(
                ['username' => $username],
                [
                    '$set' => [
                        'password'   => $password,
                        'first_name' => $firstName,
                        'last_name'  => $lastName,
                    ],
                ]
            );
        } else {
            $this->collection('user_table')->insert([
                'username'   => $username,
                'password'   => $password,
                'first_name' => $firstName,
                'last_name'  => $lastName,
            ]);
        }

        return true;
    }
}
