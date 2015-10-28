<?php namespace Tests\unit\ApiExceptions;


use Mockery as m;
use MyAssistant\CoreBundle\Exception\Api\NotFoundException;

class NotFoundExceptionTest extends ApiExceptionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getException()
    {
        return new NotFoundException(self::$message);
    }

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
        $this->assertEquals('notFound', $this->exception->getReason());
    }
}