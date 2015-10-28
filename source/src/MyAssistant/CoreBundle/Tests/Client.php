<?php namespace MyAssistant\CoreBundle\Tests;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * @var Connection
     */
    static protected $connection;

    protected function doRequest($request)
    {
        $this->injectConnection();

        self::$connection->beginTransaction();
        $response = parent::doRequest($request);
        self::$connection->rollback();

        return $response;
    }

    protected function injectConnection()
    {
        $this->kernel->boot();
        $container = $this->getContainer();
        self::$connection = $container->get('doctrine.dbal.default_connection');
    }

}