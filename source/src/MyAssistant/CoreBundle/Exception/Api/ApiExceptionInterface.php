<?php  namespace MyAssistant\CoreBundle\Exception\Api;


interface ApiExceptionInterface
{
    /**
     * @return string
     */
    public function getReason();

    /**
     * @return array
     */
    public function getAdditionalData();
}