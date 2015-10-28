<?php namespace MyAssistant\BudgetBundle\Tests\Api\v1;

use MyAssistant\BudgetBundle\Entity\PurchaseRepository;
use MyAssistant\BudgetBundle\Tests\Api\v1\WebTestCase;
use FOS\RestBundle\Util\Codes;

class PurchaseControllerTest extends WebTestCase
{
    /**
     * @var PurchaseRepository
     */
    private $repository;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->repository = $this->em->getRepository('MyAssistant\BudgetBundle\Entity\Purchase');
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * CGET
     * -----------------------------------------------------------------------------------------------------------------
     */

    // Purchase dimension ---------------------------------------------------------------------------------------------

    public function testCgetAction()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $response = $this->getResponse($uri);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT count(DISTINCT ',
            'SELECT DISTINCT',
            'SELECT'
        ]);

        return $responseAsArray;
    }

    public function testCgetActionWithLimit()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $response = $this->getResponse($uri, 'GET', [], ['limit' => 5]);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(5, count($responseAsArray['items']));
        $this->assertEquals(5, $responseAsArray['num_items_per_page']);

        return $responseAsArray;
    }

    /**
     * @depends testCgetAction
     */
    public function testCgetReturnsCorrectMetaAndItems(array $responseAsArray)
    {
        $this->assertEquals(15, $responseAsArray['total_count']);
        $this->assertEquals(10, $responseAsArray['num_items_per_page']);
        $this->assertEquals(1, $responseAsArray['page']);
        $this->assertEquals(10, count($responseAsArray['items']));
    }

    /**
     * @depends testCgetAction
     */
    public function testCgetOrderByDefaultByDayAscDirection(array $responseAsArray)
    {
        $this->assertNotEmpty($responseAsArray);
        $beforeDay = $responseAsArray['items'][0]['bought_at'];

        foreach ($responseAsArray['items'] as $row) {
            $this->assertTrue($beforeDay >= $row['bought_at'],
                'There is no DESC order by bought_at field. Failed: '
                . $row['bought_at']
                . ', previous day: '
                . $beforeDay);
            $beforeDay = $row['bought_at'];
        }
    }

    public function testCgetFiltersByDate()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $response = $this->getResponse($uri, 'GET', [], ['date' => '2015-02']);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT count(DISTINCT ',
            'SELECT DISTINCT',
            'SELECT'
        ]);
        $this->assertCount(4, $responseAsArray['items']);

        return $responseAsArray;
    }

    // Day dimension --------------------------------------------------------------------------------------------------

    public function testCgetActionForDayDimension()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $response = $this->getResponse($uri, 'GET', null, ['dimension' => 'day']);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT COUNT(*)',
            'SELECT DISTINCT',
            'SELECT', // for grouped data by bought_at
            'SELECT' // select all data for days in previous select
        ]);

        return $responseAsArray;
    }

    /**
     * @depends testCgetActionForDayDimension
     */
    public function testCgetReturnsCorrectMetaAndItemsForDayDimension(array $responseAsArray)
    {
        $this->assertEquals(3, $responseAsArray['total_count']);
        $this->assertEquals(10, $responseAsArray['num_items_per_page']);
        $this->assertEquals(1, $responseAsArray['page']);
        $this->assertEquals(3, count($responseAsArray['items']));
    }

    /**
     * @depends testCgetActionForDayDimension
     */
    public function testCgetOrderByDefaultByDayAscDirectionForDayDimension(array $responseAsArray)
    {
        $this->assertNotEmpty($responseAsArray);
        $beforeDay = key($responseAsArray['items']);

        foreach ($responseAsArray['items'] as $afterDay => $row) {
            $this->assertTrue($beforeDay >= $afterDay,
                'There is no DESC order by bought_at field. Failed: '
                . $afterDay
                . ', previous day: '
                . $beforeDay);
            $beforeDay = $afterDay;
        }
    }

    public function testCgetFiltersByDateForDayDimension()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $response = $this->getResponse($uri, 'GET', null, ['dimension' => 'day', 'date' => '2015-02']);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT COUNT(*)',
            'SELECT DISTINCT',
            'SELECT', // for grouped data by bought_at
            'SELECT' // select all data for days in previous select
        ]);
    }

    public function testCgetFiltersByDateForDayDimensionWithInvalidDate()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $response = $this->getResponse($uri, 'GET', null, ['dimension' => 'day', 'date' => 'qqqwww']);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            'code' => Codes::HTTP_BAD_REQUEST,
            'message' => 'Invalid date parameter. Date format should be Y-m format.'
        ], $responseAsArray);

        return $responseAsArray;
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * GET
     * -----------------------------------------------------------------------------------------------------------------
     */
    public function testGetAction()
    {
        $uri = self::URI_PREFIX . 'purchases/15';
        $response = $this->getResponse($uri);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        /**
         * @see MyAssistant\BudgetBundle\DataFixtures\ORM\LoadPurchaseData::createMilkPurchase()
         */
        $this->assertEquals([
            'id' => 15,
            'title' => 'Milk',
            'amount' => 5,
            'price' => 100.99,
            'bought_at' => '1420070400'
        ], $responseAsArray);
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT'
        ]);
    }

    public function testGetActionWithNotValidId()
    {
        $uri = self::URI_PREFIX . 'purchases/999999';
        $response = $this->getResponse($uri);
        $responseAsArray = json_decode($response->getContent());

        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * POST
     * -----------------------------------------------------------------------------------------------------------------
     */
    public function testPostAction()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'Some Unique Product',
            'amount' => 20,
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];
        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_CREATED, $response->getStatusCode());
        $this->assertArraySubset($data, $responseAsArray);
        $this->assertQueries([
            '"START TRANSACTION"',
            'INSERT'
        ]);
    }

    public function testPostActionRequireTitle()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'amount' => 20,
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'title',
                'message' => 'This value should not be blank.'

            ]
        ], $responseAsArray);
    }

    public function testPostActionTitleMax255()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => str_repeat('a', 256),
            'amount' => 20,
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'title',
                'message' => 'This value is too long. It should have 255 characters or less.'

            ]
        ], $responseAsArray);
    }

    public function testPostActionRequireAmount()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'amount',
                'message' => 'This value should not be blank.'

            ]
        ], $responseAsArray);
    }

    public function testPostActionAmountMin1()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'amount' => 0,
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'amount',
                'message' => 'Amount could not be less than 1.'

            ]
        ], $responseAsArray);
    }

    public function testPostActionAmountMax9999999()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'amount' => 10000000,
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'amount',
                'message' => 'Amount could not be more than 9999999.'

            ]
        ], $responseAsArray);
    }

    public function testPostActionAmountShouldBeInteger()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'amount' => 'qweqe',
            'price' => 999.99,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'amount',
                'message' => 'Amount could not be less than 1.'

            ]
        ], $responseAsArray);
    }

    public function testPostActionRequirePriceAsDecimal()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'amount' => 1,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'price',
                'message' => 'This value should not be blank.'
            ],
        ], $responseAsArray);
    }

    public function testPostActionPriceShouldBeDecimal()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'price' => 'qwe',
            'amount' => 1,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'price',
                'message' => 'This value should be a valid number.'
            ]
        ], $responseAsArray);
    }

    public function testPostActionPriceMax()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'price' => 100000000000,
            'amount' => 1,
            'bought_at' => '1436214660'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'price',
                'message' => 'Amount could not be more than 9999999999.'
            ]
        ], $responseAsArray);
    }

    public function testPostActionRequireBoughtAt()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'price' => 99.99,
            'amount' => 1
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'boughtAt',
                'message' => 'This value should not be blank.'
            ]
        ], $responseAsArray);
    }

    public function testPostActionBoughtAtShouldBeValidTimestamp()
    {
        $uri = self::URI_PREFIX . 'purchases';
        $data = [
            'title' => 'title',
            'price' => 99.99,
            'amount' => 1,
            'bought_at' => 'asd'
        ];

        $response = $this->getResponse($uri, 'POST', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            [
                'property_path' => 'boughtAt',
                'message' => 'This value should be a valid date.'
            ]
        ], $responseAsArray);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * PUT
     * -----------------------------------------------------------------------------------------------------------------
     */
    public function testPutAction()
    {
        $uri = self::URI_PREFIX . 'purchases/15';
        $data = [
            'id' => 15,
            'title' => 'Milk2',
            'price' => 99.99,
            'amount' => 100,
            'bought_at' => '1436388113'
        ];

        $response = $this->getResponse($uri, 'PUT', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($data, $responseAsArray);
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT',
            'UPDATE'
        ]);
    }


    public function testPutActionWithoutId()
    {
        $uri = self::URI_PREFIX . 'purchases/15';
        $data = [
            'title' => 'Milk2',
            'price' => 99.99,
            'amount' => 100,
            'bought_at' => '1436388113'
        ];

        $response = $this->getResponse($uri, 'PUT', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(array_merge($data, ['id' => 15]), $responseAsArray);
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT',
            'UPDATE'
        ]);
    }

    public function testPutActionWithInvalidId()
    {
        $invalidId = 999111;
        $uri = self::URI_PREFIX . 'purchases/' . 999111;
        $data = [
            'title' => 'Milk2',
            'price' => 99.99,
            'amount' => 100,
            'bought_at' => '1436388113'
        ];

        $response = $this->getResponse($uri, 'PUT', json_encode($data));

        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());

        $dbProfile = $this->client->getProfile()->getCollector('db');

        /**
         * Start transaction
         * Select
         */
        $this->assertEquals(2, $dbProfile->getQueryCount());
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * PATCH
     * -----------------------------------------------------------------------------------------------------------------
     */
    public function testPatchAction()
    {
        $uri = self::URI_PREFIX . 'purchases/15';
        $data = ['title' => 'Milk2'];

        $response = $this->getResponse($uri, 'PATCH', json_encode($data));
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertEquals([
            'id' => 15,
            'title' => 'Milk2',
            'amount' => '5',
            'price' => '100.99',
            'bought_at' => '1420070400'
        ], $responseAsArray);
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT',
            'UPDATE'
        ]);
    }

    public function testPatchActionWithInvalidId()
    {
        $invalidId = 999111;
        $uri = self::URI_PREFIX . 'purchases/' . 999111;
        $data = ['title' => 'Milk2'];

        $response = $this->getResponse($uri, 'PUT', json_encode($data));

        $this->assertEquals(Codes::HTTP_NOT_FOUND, $response->getStatusCode());

        $dbProfile = $this->client->getProfile()->getCollector('db');
        /**
         * Start transaction
         * Select
         */
        $this->assertEquals(2, $dbProfile->getQueryCount());
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * DELETE
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testDeleteAction()
    {
        $uri = self::URI_PREFIX . 'purchases/15';
        $response = $this->getResponse($uri, 'DELETE');

        $this->assertEquals(Codes::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT',
            'DELETE'
        ]);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * SUM
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testSumAction()
    {
        $uri = self::URI_PREFIX . 'purchases/sum';
        $response = $this->getResponse($uri);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT',
        ]);

        $this->assertEquals(10 * 5 + 15 * 5 + 5 * 4 + 100.99, $responseAsArray);
    }

    public function testSumActionForMonth()
    {
        $uri = self::URI_PREFIX . 'purchases/sum';
        $response = $this->getResponse($uri, 'GET', null, ['date' => '2015-02']);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertQueries([
            '"START TRANSACTION"',
            'SELECT',
        ]);

        $this->assertEquals(5 * 4, $responseAsArray);
    }

    public function testSumActionForInvalidMonthDateShouldFail()
    {
        $uri = self::URI_PREFIX . 'purchases/sum';
        $response = $this->getResponse($uri, 'GET', null, ['date' => 'aaaaa']);
        $responseAsArray = json_decode($response->getContent(), true);

        $this->assertEquals(Codes::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            'code' => Codes::HTTP_BAD_REQUEST,
            'message' => 'Invalid date parameter. Date format should be Y-m format.'
        ], $responseAsArray);
    }
}
