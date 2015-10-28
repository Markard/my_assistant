<?php


class ResendEmailConfirmationCest
{
    public function testRouteShouldExist(FunctionalTester $I)
    {
        $I->sendPUT('email/test_user_with_confirmation@gmail.com/resend_confirmation_code');

        /**
         * Assertions
         */
        $I->dontSeeResponseCodeIs(404);
    }

    public function testResendEmailShouldSucceed(FunctionalTester $I)
    {
        $I->loadDump();

        $email = 'test_user1@gmail.com';
        $I->updateEmailConfirmationCreatedAt(Carbon\Carbon::yesterday());
        $I->sendPUT('email/' . $email . '/resend_confirmation_code');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'message' => 'Confirmation code was successfully sent to your email.'
        ]);

        $message = $I->getEmailMessage();
        $I->assertEquals('Email Confirmation', $message->getSubject());
        $I->assertEquals('send@example.com', key($message->getFrom()));
        $I->assertEquals('test_user1@gmail.com', key($message->getTo()));

        preg_match('/Confirmation code: ([^\n]+)$/', $message->getBody(), $matches);
        $I->assertNotEmpty($matches[1]);
        $I->seeInDatabase('email_confirmation', [
            'confirmation_code' => $matches[1],
            'email' => $email
        ]);
    }

    public function testResendToInvalidEmailShouldFailed(FunctionalTester $I)
    {
        $I->sendPUT('email/invalid@email.com/resend_confirmation_code');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Email not found.',
            'reason' => 'notFound'
        ]);
    }

    public function testResendTooOftenShouldFailed(FunctionalTester $I)
    {
        $I->loadDump();

        $I->expect('Trying to resend confirm early than 10 minutes after last resend should fails.');
        $email = 'test_user1@gmail.com';
        $I->updateEmailConfirmationCreatedAt(Carbon\Carbon::now());
        $I->sendPUT('email/' . $email . '/resend_confirmation_code');

        /**
         * Assertions
         */
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'You already resend confirmation code. Please wait a little.',
            'reason' => 'resendTimeout'
        ]);
    }
}
