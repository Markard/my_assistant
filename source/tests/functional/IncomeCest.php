<?php


class IncomeCest
{
    /**
     * -----------------------------------------------------------------------------------------------------------------
     * List
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testListActionAuthentication(FunctionalTester $I)
    {
        $I->expect('To get list of user income he has to be authenticated.');
        $I->sendGET('incomes');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs('401');
    }

    public function testListAction(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes');

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
        $I->assertEquals(14, $totalCount[0]);

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
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].price');
        $I->canSeeResponseJsonMatchesJsonPath('$.items[0].date');

        $I->expect('Response has correct sort. By default we should sort by date at DESC');
        $beforeDay = $item[0][0]['date'];

        foreach ($item[0] as $row) {
            $I->assertTrue($beforeDay >= $row['date'],
                'There is no DESC order by date field. Failed: '
                . $row['date']
                . ', previous day: '
                . $beforeDay);
            $beforeDay = $row['date'];
        }
    }

    public function testListActionWithLimit(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['limit' => 5]);

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
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['limit' => 'limit']);

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
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['limit' => 1000000000]);

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
        $I->assertEquals(14, count($items[0]));
    }

    public function testListActionWithPage(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['page' => 2]);

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
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['page' => 'page']);

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
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['page' => 999]);

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
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['sort' => 'some_invalid_field']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testListActionWithInvalidDirectionParameterShouldUseDefaultValue(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['direction' => 'some_invalid_value']);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function testListWithFilterByDate(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes', ['date' => '2015-02']);

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
     * Get
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function testGetActionAuthentication(FunctionalTester $I)
    {
        $I->expect('In order get details about user income he has to be authenticated.');
        $I->sendGET('incomes/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs('401');
    }

    public function testGetAction(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => 1,
            'title' => 'salary',
            'price' => '10.50',
            'date' => '2015-01-01'
        ]);
    }

    public function testGetActionByAnotherUser(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome2');
        $I->sendGET('incomes/15');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'id' => 15,
            'title' => 'salary',
            'price' => '401.24',
            'date' => '2015-01-01'
        ]);
    }

    public function testGetWithNotExistedId(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes/99999999');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
    }

    public function testGetNotYourIncome(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes/15');

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
        $I->expect('In order to create income user has to be authenticated.');
        $I->sendPOST('incomes');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs('401');
    }

    public function testCreateIncome(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Some Unique Product',
                'price' => '999.99',
                'date' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs('201');

        $maxId = $I->grabFromDatabase('income', 'MAX(id)');
        $I->seeHttpHeader('Location', $I->generateUrl('get_income', ['id' => $maxId], true));

        $I->seeResponseContainsJson([
            'message' => 'You successfully created income.',
            'data' => [
                'id' => $maxId,
                'title' => 'Some Unique Product',
                'price' => '999.99',
                'date' => '2015-09-30',
            ],
        ]);
    }

    public function testCreateIncomeOverUserMonthLimitShouldFailed(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $before = $I->grabFromDatabase('income', 'COUNT(*)');

        $I->authenticated('TestUserWithIncome1');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $I->getEm();
        /** @var \MyAssistant\AuthJwtBundle\Entity\User $user */
        $user = $em->getRepository('MyAssistant\AuthJwtBundle\Entity\User')
           ->findOneBy(['username' => 'TestUserWithIncome1']);
        $user->setIncomesPerMonth(1000);
        $em->flush($user);

        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Some Unique Product',
                'price' => '999.99',
                'date' => '2015-09-30'
            ]
        ]);
        $after = $I->grabFromDatabase('income', 'COUNT(*)');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs('400');
        $I->seeResponseContainsJson([
            'message' => "You exceed your month limit. You can't create income in this month.",
            'reason' => 'error',
        ]);
        $I->assertEquals($before, $after);
    }

    public function testCreateIncomeOnlyForCurrentUser(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Some Unique Product',
                'price' => '999.99',
                'date' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $maxId = $I->grabFromDatabase('income', 'MAX(id)');
        $userId = $I->grabFromDatabase('income', 'user_id', ['id' => $maxId]);
        $username = $I->grabFromDatabase('user', 'username', ['id' => $userId]);

        $I->assertEquals('TestUserWithIncome1', $username);
    }

    public function testCreateIncomeRequireParams(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => []
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
                    'price' => 'This value should not be blank.',
                    'date' => 'This value should not be blank.',
                ]
            ]
        ]));
    }

    public function testCreateIncomeTitleLength(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => str_repeat('a', 256),
                'price' => '999.99',
                'date' => '2015-09-30'
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

    public function testCreateIncomePriceShouldBeFloat(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Title',
                'price' => 'wer',
                'date' => '2015-09-30'
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

    public function testCreateIncomePriceShouldNotBeLessThan0(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Title',
                'price' => '-1',
                'date' => '2015-09-30'
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

    public function testCreateIncomePriceShouldNotBeMoreThan9999999999(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Title',
                'price' => '10000000000',
                'date' => '2015-09-30'
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

    public function testCreateIncomeDateShouldBeDate(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPOST('incomes', [
            'income' => [
                'title' => 'Title',
                'price' => '1',
                'date' => 'ffff'
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
                    "date" => "This value should be date like 2015-01-01."
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
        $I->expect('In order to update income user has to be authenticated.');
        $I->sendPUT('incomes/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testPutIncome(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPUT('incomes/1', [
            'income' => [
                'title' => 'New Milk',
                'price' => '999.99',
                'date' => '2015-09-30'
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'message' => 'You successfully updated income.',
            'data' => [
                'id' => 1,
                'title' => 'New Milk',
                'price' => '999.99',
                'date' => '2015-09-30'
            ]
        ]);
        $I->seeInDatabase('income', [
            'title' => 'New Milk',
            'price' => '999.99',
            'date' => '2015-09-30'
        ]);
    }

    public function testPutNotYourIncomeShouldFails(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendPUT('incomes/15', [
            'income' => [
                'title' => 'New Milk',
                'price' => '999.99',
                'date' => '2015-09-30'
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
        $I->expect('In order to update income user has to be authenticated.');
        $I->sendPATCH('incomes/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testPatchIncome(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendPATCH('incomes/1', [
            'income' => [
                'title' => 'New Milk',
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'message' => 'You successfully updated income.',
            'data' => [
                'id' => 1,
                'title' => 'New Milk',
                'price' => '10.50',
                'date' => '2015-01-01'
            ]
        ]);
        $I->seeInDatabase('income', [
            'title' => 'New Milk',
            'price' => '10.50',
            'date' => '2015-01-01'
        ]);
    }

    public function testPatchNotYourIncomeShouldFails(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendPATCH('incomes/16', [
            'income' => [
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
        $I->sendDELETE('incomes/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testDeleteIncome(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendDELETE('incomes/1');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['message' => 'You successfully deleted income.']);
        $I->dontSeeInDatabase('income', ['id' => 1]);
    }

    public function testDeleteNotYourIncomeShouldFails(FunctionalTester $I)
    {
        $I->authenticated('TestUserWithIncome1');
        $I->sendPATCH('incomes/16', [
            'income' => [
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
        $I->expect('In order to get income sum user has to be authenticated.');
        $I->sendGET('incomes/sum');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(401);
    }

    public function testSumAction(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes/sum', [
            'date' => '2015-01'
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => ['sum' => '52.50']]);
    }

    public function testSumActionForTotalPeriod(FunctionalTester $I)
    {
        $I->expectDatabaseChange();
        $I->authenticated('TestUserWithIncome1');
        $I->sendGET('incomes/sum');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['data' => ['sum' => '981.50']]);
    }
}
