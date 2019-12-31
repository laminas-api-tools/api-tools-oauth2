<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-oauth2 for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\OAuth2\Factory;

use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Controller\Exception;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class MongoAdapterFactory
 *
 * @package Laminas\ApiTools\OAuth2\Factory
 * @author Chuck "MANCHUCK" Reeves <chuck@manchuck.com>
 */
class MongoAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws \Laminas\ApiTools\OAuth2\Controller\Exception\RuntimeException
     * @return \Laminas\ApiTools\OAuth2\Adapter\PdoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config  = $services->get('Configuration');

        $dbLocatorName = isset($config['api-tools-oauth2']['mongo']['locator_name'])
            ? $config['api-tools-oauth2']['mongo']['locator_name']
            : 'MongoDB';

        if ($services->has($dbLocatorName)) {
            $connection = $services->get($dbLocatorName);
        } else {


            if (!isset($config['api-tools-oauth2']['mongo']) || empty($config['api-tools-oauth2']['mongo']['database'])) {
                throw new Exception\RuntimeException(
                    'The database configuration [\'api-tools-oauth2\'][\'mongo\'] for OAuth2 is missing'
                );
            }

            $server     = isset($config['api-tools-oauth2']['mongo']['dsn']) ? $config['api-tools-oauth2']['mongo']['dsn'] : null;
            $mongo      = new \MongoClient($server, array('connect' => false));
            $connection = $mongo->{$config['api-tools-oauth2']['mongo']['database']};
        }

        return new MongoAdapter($connection);
    }
}
