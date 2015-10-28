<?php namespace Tests\unit\ApiExceptions;


use Mockery as m;
use MyAssistant\CoreBundle\Exception\Api\FormValidationException;

class FormValidationExceptionTest extends ApiExceptionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getException()
    {
        $formMock = m::mock('Symfony\Component\Form\Form');
        $formMock->shouldReceive('getErrors')
                 ->atMost()
                 ->andReturn([1, 2, 3]);

        return new FormValidationException($formMock, self::$message);
    }

    public function testReason()
    {
        /**
         * Assertions
         */
        $this->assertEquals('formValidationFailed', $this->exception->getReason());
    }

    public function testAdditionalData()
    {
        //@todo
    }
}