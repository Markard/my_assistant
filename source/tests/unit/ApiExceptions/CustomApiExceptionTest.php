<?php namespace Tests\unit\ApiExceptions;


use MyAssistant\CoreBundle\Exception\Api\CustomApiException;

class CustomApiExceptionTest extends ApiExceptionTest
{
    public function testAdditionalDataIsEmpty()
    {
        /**
         * Assertions
         */
        $this->assertEmpty($this->exception->getAdditionalData());
    }

    public function testReason()
    {
        /**
         * Assertions
         */
        $this->assertEquals('error', $this->exception->getReason());
    }

    /**
     * {@inheritdoc}
     */
    protected function getException()
    {
        return new CustomApiException(self::$message);
    }
}