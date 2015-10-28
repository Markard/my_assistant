<?php


class EmailConfirmationCest
{
    public function testEmailConfirmationRouteShouldExist(FunctionalTester $I)
    {
        $I->sendDELETE('email/test@email.com/confirm/123');

        /**
         * Assertions
         */
        $I->dontSeeResponseCodeIs(404);
    }

    public function testEmailConfirmationShouldConfirmEmail(FunctionalTester $I)
    {
        $I->updateEmailConfirmationCreatedAt(Carbon\Carbon::now());
        $I->sendDELETE('email/test_user1@gmail.com/confirm/123qwe');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.data.token');
        $I->seeResponseJsonMatchesJsonPath('$.message');

        $message = $I->grabDataFromResponseByJsonPath('$.message');
        $I->assertEquals('Email successfully confirmed.', $message[0]);
    }

    public function testEmailConfirmationWithInvalidEmailShouldFailed(FunctionalTester $I)
    {
        $I->updateEmailConfirmationCreatedAt(Carbon\Carbon::now());
        $I->sendDELETE('email/invalid_email@gmail.com/confirm/123qwe');

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
                    'confirmation_code' => 'Confirmation code is invalid. You can try to resend email once more.',
                ]
            ]
        ]));
    }

    public function testEmailConfirmationWithInvalidCodeShouldFailed(FunctionalTester $I)
    {
        $I->loadDump();

        $I->updateEmailConfirmationCreatedAt(Carbon\Carbon::now());
        $I->sendDELETE('email/test_user1@gmail.com/confirm/invalidcode');

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
                    'confirmation_code' => 'Confirmation code is invalid. You can try to resend email once more.',
                ]
            ]
        ]));
    }

    public function testEmailConfirmationForExpiredCodeShouldFailed(FunctionalTester $I)
    {
        $I->loadDump();

        $I->updateEmailConfirmationCreatedAt(Carbon\Carbon::yesterday());
        $I->sendDELETE('email/test_user1@gmail.com/confirm/123qwe');

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
                    'confirmation_code' => 'Confirmation code is expired. You can try to resend email once more.',
                ]
            ]
        ]));
    }
}
