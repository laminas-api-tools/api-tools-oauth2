<?php

declare(strict_types=1);

namespace Laminas\ApiTools\OAuth2\Adapter;

use Laminas\Crypt\Password\Bcrypt;
use OAuth2\Storage\Pdo as OAuth2Pdo;

use function func_num_args;
use function sprintf;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends OAuth2Pdo
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
     * @param array<array-key, mixed> $user
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
     * @param string $connection
     * @param array $config
     */
    public function __construct($connection, $config = [])
    {
        parent::__construct($connection, $config);
        if (isset($config['bcrypt_cost'])) {
            $this->setBcryptCost($config['bcrypt_cost']);
        }
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
        $stmt = $this->db->prepare(sprintf(
            'SELECT * from %s where client_id = :client_id',
            $this->config['client_table']
        ));
        $stmt->execute(['client_id' => $clientId]);
        $result = $stmt->fetch();

        // Do not bother verifying if the secret is missing or empty.
        if (! isset($result['client_secret']) || empty($result['client_secret'])) {
            return false;
        }

        // bcrypt verify
        return $this->verifyHash($clientSecret, $result['client_secret']);
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
        // if it exists, update it.
        if ($this->getClientDetails($clientId)) {
            $stmt = $this->db->prepare(sprintf(
                <<<'END'
                    UPDATE %s
                    SET
                      client_secret=:client_secret,
                      redirect_uri=:redirect_uri,
                      grant_types=:grant_types,
                      scope=:scope,
                      user_id=:user_id
                    WHERE client_id=:client_id
                    END,
                $this->config['client_table']
            ));
        } else {
            $stmt = $this->db->prepare(sprintf(
                <<<'END'
                    INSERT INTO %s (
                        client_id,
                        client_secret,
                        redirect_uri,
                        grant_types,
                        scope,
                        user_id
                    )
                    VALUES (
                        :client_id,
                        :client_secret,
                        :redirect_uri,
                        :grant_types,
                        :scope,
                        :user_id
                    )
                    END,
                $this->config['client_table']
            ));
        }
        return $stmt->execute([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_types'   => $grantTypes,
            'scope'         => $scope,
            'user_id'       => $userId,
        ]);
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
        // do not store in plaintext, use bcrypt
        $this->createBcryptHash($password);

        // if it exists, update it.
        if ($this->getUser($username)) {
            $stmt = $this->db->prepare(sprintf(
                'UPDATE %s SET password=:password, first_name=:firstName, last_name=:lastName where username=:username',
                $this->config['user_table']
            ));
        } else {
            $stmt = $this->db->prepare(sprintf(
                'INSERT INTO %s (username, password, first_name, last_name) '
                . 'VALUES (:username, :password, :firstName, :lastName)',
                $this->config['user_table']
            ));
        }

        return $stmt->execute([
            'username'  => $username,
            'password'  => $password,
            'firstName' => $firstName,
            'lastName'  => $lastName,
        ]);
    }
}
