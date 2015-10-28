<?php namespace MyAssistant\CoreBundle\Exception\Api;


use Exception;

class ManuallyFormException extends FormValidationException
{
    /**
     * @var array
     */
    protected $globalErrors = [];

    /**
     * @var array
     */
    protected $fieldsErrors = [];

    public function __construct(
        $globalErrors = [],
        $fieldsErrors = [],
        $message = "Invalid submitted data",
        $code = 0,
        Exception $previous = null
    ) {
        $this->message = $message;
        $this->globalErrors = $globalErrors;
        $this->fieldsErrors = $fieldsErrors;
    }

    public function getAdditionalData()
    {
        return [
            'global' => $this->globalErrors ?: [],
            'fields' => $this->fieldsErrors ?: []
        ];
    }
}