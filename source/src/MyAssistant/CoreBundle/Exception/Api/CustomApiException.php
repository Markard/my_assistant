<?php  namespace MyAssistant\CoreBundle\Exception\Api;


class CustomApiException extends  ApiException
{
    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'error';
    }
}