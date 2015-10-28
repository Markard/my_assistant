<?php namespace MyAssistant\CoreBundle\Exception\Api;


abstract class ApiException extends \Exception implements ApiExceptionInterface
{
    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return [];
    }
}