<?php namespace MyAssistant\BudgetBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Give access to client and entity manager.
 * Implement transactions and rollback so every test will be independent
 * from another
 */
class WebTestCase extends BaseWebTestCase
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = static::createClient();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->em->getConnection()->rollback();
        $this->em->close();
    }

    protected function getResponse($uri, $method = 'GET', $content = null, $parameters = [])
    {
        $this->client->request($method, $uri, $parameters, [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ], $content);

        return $this->client->getResponse();
    }

    protected function assertQueries(array $queries, $connection = 'default')
    {
        /** @var DoctrineDataCollector $dbProfile */
        $dbProfile = $this->client->getProfile()->getCollector('db');

        $this->assertEquals(count($queries), $dbProfile->getQueryCount());
        $i = 0;
        foreach ($dbProfile->getQueries()[$connection] as $query) {
            $this->assertStringStartsWith($queries[$i], $query['sql']);
            $i++;
        }
    }

    protected function assertDescIdOrder(array $rows)
    {
        $this->assertNotEmpty($rows);
        $firstRow = $rows[0];

        foreach($rows as $row) {
            $this->assertTrue($firstRow['bought_at'] >= $row['bought_at'],
                'There is no DESC order by bought_at field. Row id: '
                . $row['bought_at']
                . ', previous row id: '
                . $firstRow['bought_at']);
        }
    }

}