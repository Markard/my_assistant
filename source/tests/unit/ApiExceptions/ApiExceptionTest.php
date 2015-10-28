<?php namespace Tests\unit\ApiExceptions;


use MyAssistant\CoreBundle\Exception\Api\ApiExceptionInterface;
use Tests\unit\BaseTest;

abstract class ApiExceptionTest extends BaseTest
{
    public static $message = 'Some exception message.';

    /**
     * @var ApiExceptionInterface
     */
    protected $exception;

    protected function _before()
    {
        $this->exception = $this->getException();
    }

    /**
     * @return ApiExceptionInterface
     */
    abstract protected function getException();

    public function testInstanceOfApiExceptionInterface()
    {
        /**
         * Assertions
         */
        $this->assertInstanceOf('MyAssistant\CoreBundle\Exception\Api\ApiExceptionInterface', $this->exception);
    }

    public function testMessage()
    {
        /**
         * Assertions
         */
        $this->assertEquals(self::$message, $this->exception->getMessage());
    }
}