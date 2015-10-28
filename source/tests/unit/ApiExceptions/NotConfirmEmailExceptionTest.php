<?php namespace Tests\unit\ApiExceptions;


use Mockery as m;
use MyAssistant\AuthJwtBundle\Exception\Api\NotConfirmEmailException;

class NotConfirmEmailExceptionTest extends ApiExceptionTest
{
    public static $email = 'test@gmail.com';

    /**
     * {@inheritdoc}
     */
    protected function getException()
    {
        $userMock = m::mock('MyAssistant\AuthJwtBundle\Entity\User');
        $userMock
            ->shouldReceive('getEmail')
            ->atMost(1)
            ->andReturn(self::$email);

        return new NotConfirmEmailException($userMock, self::$message);
    }

    public function testInstanceOfAuthenticationException()
    {
        /**
         * Assertions
         */
        $this->assertInstanceOf('Symfony\Component\Security\Core\Exception\AuthenticationException', $this->exception);
    }

    public function testAdditionalData()
    {
        /**
         * Assertions
         */
        $this->assertEquals(['email' => self::$email], $this->exception->getAdditionalData());
    }

    public function testReason()
    {
        /**
         * Assertions
         */
        $this->assertEquals('emailNotConfirmed', $this->exception->getReason());
    }
}