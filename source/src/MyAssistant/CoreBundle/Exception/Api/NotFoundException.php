<?php  namespace MyAssistant\CoreBundle\Exception\Api;


class NotFoundException extends ApiException
{
    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'notFound';
    }
}