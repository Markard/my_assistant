<?php


class AuthenticationCest
{
    public function testAuthenticationRouteShouldExist(FunctionalTester $I)
    {
        $I->sendPOST('get_token');

        /**
         * Assertions
         */
        $I->dontSeeResponseCodeIs(404);
    }

    public function testGettingTokenWithValidCredentials(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'username' => 'TestUser1',
            'password' => '12345'
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.token');
    }

    public function testGettingTokenWithEmailAsUsername(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'username' => 'test_user1@gmail.com',
            'password' => '12345'
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.token');
    }

    public function testGettingTokenWithInvalidPasswordPropertyShouldFailed(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'username' => 'TestUser1',
            'password' => 'some invalid password'
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
                'global' => [
                    'Bad credentials.'
                ],
                'fields' => []
            ]
        ]));
    }

    public function testGettingTokenWithNotExistingUsernameShouldFailed(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'username' => 'some not existing username',
            'password' => 'some invalid password'
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
                'global' => [
                    'Bad credentials.'
                ],
                'fields' => []
            ]
        ]));
    }

    public function testGettingTokenWithoutUsernamePropertyShouldFailed(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'password' => '12345'
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
                'global' => [
                    'Bad credentials.'
                ],
                'fields' => []
            ]
        ]));
    }

    public function testGettingTokenWithoutPasswordPropertyShouldFailed(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'username' => '12345'
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
                'global' => [
                    'Bad credentials.'
                ],
                'fields' => []
            ]
        ]));
    }

    public function testGettingTokenForUserWithoutEmailConfirmationShouldFailed(FunctionalTester $I)
    {
        $I->sendPOST('get_token', [
            'username' => 'TestUserWithConfirmation',
            'password' => '12345'
        ]);

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(json_encode([
            'message' => 'You have to confirm your email address. Confirmation code was sent to your email. '
                . 'If you did not receive confirmation code you can '
                . 'use Resend code link below.',
            'reason' => 'emailNotConfirmed',
            'data' => [
                'email' => 'test_user_with_confirmation@gmail.com'
            ]
        ]));
    }
}
