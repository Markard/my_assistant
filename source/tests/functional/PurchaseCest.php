<?php


class PurchaseCest
{
    /**
     * -----------------------------------------------------------------------------------------------------------------
     * List
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testListActionAuthentication(FunctionalTester $I)
    {
        $I->expect('To get list of user purchase he has to be authenticated.');
        $I->sendGET('purchases');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testListAction(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct metadata.');
        $I->canSeeResponseJsonMatchesJsonPath('$.total_count');
        $I->canSeeResponseJsonMatchesJsonPath('$.num_items_per_page');
        $I->canSeeResponseJsonMatchesJsonPath('$.page');
        $I->canSeeResponseJsonMatchesJsonPath('$.items');

        $I->expect('Response has correct total_count.');
        $totalCount = $I->grabDataFromResponseByJsonPath('$.total_count');
        $I->assertEquals(15, $totalCount[0]);

        $I->expect('Response has correct num_items_per_page. By default we have limit equal to 10.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(10, $numItemsPerPage[0]);

        $I->expect('Response has correct page. By default we have default page equal to 1.');
        $page = $I->grabDataFromResponseByJsonPath('$.page');
        $I->assertEquals(1, $page[0]);

        $I->expect('Response has correct items count.');
        $item = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(10, count($item[0]));

        $I->expect('Response has correct items with correct format.');
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].id');
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].title');
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].amount');
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].price');
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].bought_at');

        $I->expect('Response has correct sort. By default we should sort by bought at DESC');
        $beforeDay = $item[0][0]['bought_at'];

        foreach ($item[0] as $row) {
            $I->assertTrue($beforeDay >= $row['bought_at'],
                'There is no DESC order by bought_at field. Failed: '
                . $row['bought_at']
                . ', previous day: '
                . $beforeDay);
            $beforeDay = $row['bought_at'];
        }
    }

    public function testListActionWithLimit(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['limit' => 5]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct num_items_per_page.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(5, $numItemsPerPage[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(5, count($items[0]));
    }

    public function testListActionWithNotIntegerLimitShouldSetItTo10(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['limit' => 'limit']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct num_items_per_page.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(10, $numItemsPerPage[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(10, count($items[0]));
    }

    public function testListActionMaxLimitShouldBe100(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['limit' => 1000000000]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct num_items_per_page.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(100, $numItemsPerPage[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(15, count($items[0]));
    }

    public function testListActionWithPage(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['page' => 2]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct page.');
        $page = $I->grabDataFromResponseByJsonPath('$.page');
        $I->assertEquals(2, $page[0]);
    }

    public function testListActionWithNotIntegerPageShouldSetItTo1(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['page' => 'page']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct page.');
        $page = $I->grabDataFromResponseByJsonPath('$.page');
        $I->assertEquals(1, $page[0]);
    }

    public function testListActionWithPageWhichExceedLimitShouldNotFailed(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['page' => 999]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $r = $I->grabResponse();

        $I->expect('Response has correct page.');
        $page = $I->grabDataFromResponseByJsonPath('$.page');
        $I->assertEquals(999, $page[0]);
    }

    public function testListActionWithInvalidSortParameterShouldUseDefaultSortValue(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['sort' => 'some_invalid_field']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testListActionWithInvalidDirectionParameterShouldUseDefaultValue(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['direction' => 'some_invalid_value']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testListWithFilterByDate(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['date' => '2015-02']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct total_count.');
        $totalCount = $I->grabDataFromResponseByJsonPath('$.total_count');
        $I->assertEquals(4, $totalCount[0]);

        $I->expect('Response has correct num_items_per_page.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(10, $numItemsPerPage[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(4, count($items[0]));
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * List grouped by day dimension
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testListActionForDayDimension(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['dimension' => 'day']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct metadata.');
        $I->canSeeResponseJsonMatchesJsonPath('$.total_count');
        $I->canSeeResponseJsonMatchesJsonPath('$.num_items_per_page');
        $I->canSeeResponseJsonMatchesJsonPath('$.page');
        $I->canSeeResponseJsonMatchesJsonPath('$.items');

        $I->expect('Response has correct total_count.');
        $totalCount = $I->grabDataFromResponseByJsonPath('$.total_count');
        $I->assertEquals(3, $totalCount[0]);

        $I->expect('Response has correct num_items_per_page. By default we have limit equal to 10.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(10, $numItemsPerPage[0]);

        $I->expect('Response has correct page. Be default we have default page equal to 1.');
        $page = $I->grabDataFromResponseByJsonPath('$.page');
        $I->assertEquals(1, $page[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(3, count($items[0]));

        $I->expect('Response has correct items count for first day.');
        $I->assertEquals(4, count($items[0][0]));

        $I->expect('Response has correct items count for second day.');
        $I->assertEquals(5, count($items[0][1]));

        $I->expect('Response has correct items count for third day.');
        $I->assertEquals(6, count($items[0][2]));
    }

    public function testListForDayDimensionActionWithLimit(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['limit' => 2, 'dimension' => 'day']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct num_items_per_page.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(2, $numItemsPerPage[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(2, count($items[0]));

        $I->expect('Response has correct items count for first day.');
        $I->assertEquals(4, count($items[0][0]));

        $I->expect('Response has correct items count for second day.');
        $I->assertEquals(5, count($items[0][1]));
    }

    public function testListForDayDimensionWithFilterByDate(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases', ['date' => '2015-02', 'dimension' => 'day']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->expect('Response has correct total_count.');
        $totalCount = $I->grabDataFromResponseByJsonPath('$.total_count');
        $I->assertEquals(1, $totalCount[0]);

        $I->expect('Response has correct num_items_per_page.');
        $numItemsPerPage = $I->grabDataFromResponseByJsonPath('$.num_items_per_page');
        $I->assertEquals(10, $numItemsPerPage[0]);

        $I->expect('Response has correct items count.');
        $items = $I->grabDataFromResponseByJsonPath('$.items');
        $I->assertEquals(1, count($items[0]));
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Get
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testGetActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order get details about user purchase he has to be authenticated.');
        $I->sendGET('purchases/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testGetAction(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => 1,
            'title' => 'Milk',
            'amount' => 5,
            'price' => '100.99',
            'bought_at' => '2015-01-01'
        ]);
    }

    public function testGetActionByAnotherUser(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases2');
        $I->sendGET('purchases/16');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => 16,
            'title' => 'purchase',
            'amount' => 10,
            'price' => '10.00',
            'bought_at' => '2015-01-01'
        ]);
    }

    public function testGetWithNotExistedId(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases/99999999');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
    }

    public function testGetNotYourPurchase(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases/16');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Post
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testPostActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order to create purchase user has to be authenticated.');
        $I->sendPOST('purchases');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testCreatePurchase(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Some Unique Product',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(201);

        $maxId = $I->grabFromDatabase('purchase', 'MAX(id)');
        $I->seeHttpHeader('Location', $I->generateUrl('get_purchase', ['id' => $maxId], true));

        $I->seeResponseContainsJson([
            'message' => 'You successfully created purchase.',
            'data' => [
                'id' => $maxId,
                'title' => 'Some Unique Product',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30',
            ],
        ]);
    }

    public function testCreatePurchaseOverUserDailyLimitShouldFailed(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $before = $I->grabFromDatabase('purchase', 'COUNT(*)');

        $I->authenticated('TestUserWithPurchases1');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $I->getEm();
        /** @var \MyAssistant\AuthJwtBundle\Entity\User $user */
        $user = $em->getRepository('MyAssistant\AuthJwtBundle\Entity\User')
           ->findOneBy(['username' => 'TestUserWithPurchases1']);
        $user->setPurchasesPerDay(100);
        $em->flush($user);

        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Some Unique Product',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);
        $after = $I->grabFromDatabase('purchase', 'COUNT(*)');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'message' => "You exceed your daily limit. You can't create purchase today.",
            'reason' => 'error',
        ]);
        $I->assertEquals($before, $after);
    }

    public function testCreatePurchaseOnlyForCurrentUser(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Some Unique Product',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(201);
        $maxId = $I->grabFromDatabase('purchase', 'MAX(id)');
        $userId = $I->grabFromDatabase('purchase', 'user_id', ['id' => $maxId]);
        $username = $I->grabFromDatabase('user', 'username', ['id' => $userId]);

        $I->assertEquals('TestUserWithPurchases1', $username);
    }

    public function testCreatePurchaseRequireParams(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => []
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    'title' => 'This value should not be blank.',
                    'amount' => 'This value should not be blank.',
                    'price' => 'This value should not be blank.',
                    'bought_at' => 'This value should not be blank.',
                ]
            ]
        ]));
    }

    public function testCreatePurchaseTitleLength(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => str_repeat('a', 256),
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "title" => "This value is too long. It should have 255 characters or less."
                ]
            ]
        ]));
    }

    public function testCreatePurchaseAmountShouldBeInteger(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 'qwe',
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "amount" => "This value is not valid."
                ]
            ]
        ]));
    }

    public function testCreatePurchaseAmountShouldNotBeLessThan1(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 0,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "amount" => "Amount could not be less than 1."
                ]
            ]
        ]));
    }

    public function testCreatePurchaseAmountShouldNotBeMoreThan9999999(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 10000000,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "amount" => "Amount could not be more than 9999999."
                ]
            ]
        ]));
    }

    public function testCreatePurchasePriceShouldBeFloat(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 1,
                'price' => 'wer',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "price" => "This value is not valid."
                ]
            ]
        ]));
    }

    public function testCreatePurchasePriceShouldNotBeLessThan0(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 1,
                'price' => '-1',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "price" => "This value should be 0 or more."
                ]
            ]
        ]));
    }

    public function testCreatePurchasePriceShouldNotBeMoreThan9999999999(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 1,
                'price' => '10000000000',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "price" => "Amount could not be more than 9999999999."
                ]
            ]
        ]));
    }

    public function testCreatePurchaseBoughtAtShouldBeDate(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPOST('purchases', [
            'purchase' => [
                'title' => 'Title',
                'amount' => 1,
                'price' => '1',
                'bought_at' => 'ffff'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'Invalid submitted data',
            'reason' => 'formValidationFailed',
            'data' => [
                'global' => [],
                'fields' => [
                    "bought_at" => "This value should be date like 2015-01-01."
                ]
            ]
        ]));
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Put
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testPutActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order to update purchase user has to be authenticated.');
        $I->sendPUT('purchases/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testPutPurchase(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPUT('purchases/1', [
            'purchase' => [
                'title' => 'New Milk',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'message' => 'You successfully updated purchase.',
            'data' => [
                'id' => 1,
                'title' => 'New Milk',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);
        $I->seeInDatabase('purchase', [
            'title' => 'New Milk',
            'amount' => 20,
            'price' => '999.99',
            'bought_at' => '2015-09-30'
        ]);
    }

    public function testPutNotYourPurchaseShouldFails(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPUT('purchases/16', [
            'purchase' => [
                'title' => 'New Milk',
                'amount' => 20,
                'price' => '999.99',
                'bought_at' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Patch
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testPatchActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order to update purchase user has to be authenticated.');
        $I->sendPATCH('purchases/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testPatchPurchase(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPATCH('purchases/1', [
            'purchase' => [
                'title' => 'New Milk',
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'message' => 'You successfully updated purchase.',
            'data' => [
                'id' => 1,
                'title' => 'New Milk',
                'amount' => 5,
                'price' => '100.99',
                'bought_at' => '2015-01-01'
            ]
        ]);
        $I->seeInDatabase('purchase', [
            'title' => 'New Milk',
            'amount' => 5,
            'price' => '100.99',
            'bought_at' => '2015-01-01'
        ]);
    }

    public function testPatchNotYourPurchaseShouldFails(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPATCH('purchases/16', [
            'purchase' => [
                'title' => 'New Milk',
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * DELETE
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testDeleteActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order to delete purchase user has to be authenticated.');
        $I->sendDELETE('purchases/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testDeletePurchase(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithPurchases1');
        $I->sendDELETE('purchases/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['message' => 'You successfully deleted purchase.']);
        $I->dontSeeInDatabase('purchase', ['id' => 1]);
    }

    public function testDeleteNotYourPurchaseShouldFails(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendPATCH('purchases/16', [
            'purchase' => [
                'title' => 'New Milk',
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * SUM
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testSumActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order to get purchase sum user has to be authenticated.');
        $I->sendGET('purchases/sum');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testSumAction(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases/sum', [
            'date' => '2015-01'
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => ['sum' => '225.99']]);
    }

    public function testSumActionForTotalPeriod(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithPurchases1');
        $I->sendGET('purchases/sum');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => ['sum' => '245.99']]);
    }
}
