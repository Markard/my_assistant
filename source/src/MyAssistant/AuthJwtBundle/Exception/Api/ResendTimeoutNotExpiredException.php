<?php  namespace MyAssistant\AuthJwtBundle\Exception\Api;


use MyAssistant\CoreBundle\Exception\Api\ApiException;

class ResendTimeoutNotExpiredException extends ApiException
{
    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'resendTimeout';
    }
}