<?php namespace Tests\unit\ApiExceptions;


use MyAssistant\CoreBundle\Exception\Api\ManuallyFormException;

class ManuallyFormExceptionTest extends ApiExceptionTest
{
    public static $global = ['1', '2', '3'];
    public static $fields = ['field_1' => '1', 'field_2' => '2'];

    public function testEmptyAdditionalData()
    {
        $exception = new ManuallyFormException();

        /**
         * Assertions
         */
        $this->tester->assertEquals([
            'global' => [],
            'fields' => []
        ], $exception->getAdditionalData());
    }

    public function testAdditionalDataWithFields()
    {
        $exception = new ManuallyFormException(null, self::$fields);

        /**
         * Assertions
         */
        $this->tester->assertEquals([
            'global' => [],
            'fields' => self::$fields
        ], $exception->getAdditionalData());
    }

    public function testAdditionalDataWithGlobalFields()
    {
        $exception = new ManuallyFormException(self::$global);

        /**
         * Assertions
         */
        $this->tester->assertEquals([
            'global' => self::$global,
            'fields' => []
        ], $exception->getAdditionalData());
    }

    public function testAdditionalData()
    {
        /**
         * Assertions
         */
        $this->tester->assertEquals([
            'global' => self::$global,
            'fields' => self::$fields
        ], $this->exception->getAdditionalData());
    }

    public function testReason()
    {
        /**
         * Assertions
         */
        $this->tester->assertEquals('formValidationFailed', $this->exception->getReason());
    }

    /**
     * {@inheritdoc}
     */
    protected function getException()
    {
        return new ManuallyFormException(self::$global, self::$fields, self::$message);
    }
}