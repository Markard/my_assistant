<?php namespace Tests\unit;


use MyAssistant\AuthJwtBundle\Entity\EmailConfirmation;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\AuthJwtBundle\Security\UserChecker;

class CheckUserTest extends BaseTest
{
    /**
     * @var UserChecker
     */
    protected $checker;

    protected function _before()
    {
        $this->checker = new UserChecker();
    }

    public function testUserWithoutEmailConfirmationShouldPass()
    {
        $user = new User();
        $this->tester->assertNull($this->checker->checkPostAuth($user));
    }

    public function testUserWithEmailConfirmationShouldRaiseException()
    {
        $expectedMessage = 'You have to confirm your email address. Confirmation code was sent to your email. '
            . 'If you did not receive confirmation code you can '
            . 'use Resend code link below.';
        $this->setExpectedException('MyAssistant\AuthJwtBundle\Exception\Api\NotConfirmEmailException',
            $expectedMessage);

        $user = new User();
        $user->setEmailConfirmation(new EmailConfirmation());
        $this->checker->checkPostAuth($user);
    }

}