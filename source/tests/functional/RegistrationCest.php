<?php


class RegistrationCest
{
    public function testRegistrationRouteShouldExist(FunctionalTester $I)
    {
        $I->sendPOST('users/registration');

        /**
         * Assertions
         */
        $I->dontSeeResponseCodeIs(404);
    }

    public function testRegisterUserWithValidCredentialsShouldSucceed(FunctionalTester $I)
    {
        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'valid_username',
                'email' => 'valid_email@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '12345',
                ]
            ]
        ]);

        /**
         * Assertions
         */
        $maxId = $I->grabFromDatabase('user', 'max(id)');

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeHttpHeader('Location', 'http://127.0.0.1/api/v1/users/' . $maxId);
        $I->seeResponseContainsJson([
            'message' => 'You successfully registered. In order to finish registration you have to confirm your email.',
            'data' => [
                'id' => $maxId,
                'username' => 'valid_username',
                'email' => 'valid_email@gmail.com',
                'timezone' => 'UTC'
            ]
        ]);
    }

    public function testRegistrationRequiredFields(FunctionalTester $I)
    {
        $I->expect('username, email, password is required fields.');
        $I->sendPOST('users/registration', []);

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
                    'username' => 'This value should not be blank.',
                    'email' => 'This value should not be blank.',
                    'password_first' => 'This value should not be blank.',
                ]
            ]
        ]));
    }

    public function testUsernameShouldBeUnique(FunctionalTester $I)
    {
        $I->loadDump();

        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'TestUser1',
                'email' => 'valid_email@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '12345',
                ]
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
                    'username' => 'This value is already used.',
                ]
            ]
        ]));
    }

    public function testEmailShouldBeUnique(FunctionalTester $I)
    {
        $I->loadDump();

        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'valid_username',
                'email' => 'test_user1@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '12345',
                ]
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
                    'email' => 'This value is already used.',
                ]
            ]
        ]));
    }

    public function testPasswordShouldMatchConfirmation(FunctionalTester $I)
    {
        $I->loadDump();

        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'valid_username',
                'email' => 'valid_email@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '54321',
                ]
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
                    'password_first' => 'The password fields must match.',
                ]
            ]
        ]));
    }

    public function testRegistrationShouldCreateUserRecord(FunctionalTester $I)
    {
        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'valid_username',
                'email' => 'valid_email@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '12345',
                ]
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeInRepository('MyAssistant\AuthJwtBundle\Entity\User', [
            'username' => 'valid_username',
            'email' => 'valid_email@gmail.com',
        ]);
    }

    public function testRegistrationShouldCreateEmailConfirmationRecord(FunctionalTester $I)
    {
        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'valid_username',
                'email' => 'valid_email@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '12345',
                ]
            ]
        ]);

        /**
         * Assertions
         */
        $I->seeInRepository('MyAssistant\AuthJwtBundle\Entity\EmailConfirmation', [
            'email' => 'valid_email@gmail.com',
        ]);
    }

    public function testRegistrationShouldSendEmailWithConfirmationCode(FunctionalTester $I)
    {
        $I->loadDump();

        $I->sendPOST('users/registration', [
            'user' => [
                'username' => 'valid_username',
                'email' => 'valid_email@gmail.com',
                'password' => [
                    'first' => '12345',
                    'second' => '12345',
                ]
            ]
        ]);

        $message = $I->getEmailMessage();
        /**
         * Assertions
         */
        $I->assertEquals('Email Confirmation', $message->getSubject());
        $I->assertEquals('send@example.com', key($message->getFrom()));
        $I->assertEquals('valid_email@gmail.com', key($message->getTo()));
    }
}
