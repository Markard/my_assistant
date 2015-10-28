<?php namespace MyAssistant\AuthJwtBundle\EventListener;


use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\AuthJwtBundle\Exception\NotConfirmEmailException;
use MyAssistant\CoreBundle\Exception\Api\ApiException;
use MyAssistant\CoreBundle\Exception\Api\ApiExceptionInterface;
use MyAssistant\CoreBundle\Exception\Api\CustomApiException;
use MyAssistant\CoreBundle\Exception\Api\ManuallyFormException;
use FOS\RestBundle\Util\Codes;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AuthenticationListener
{
    public function onFailure(AuthenticationFailureEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof BadCredentialsException) {
            $exception = new ManuallyFormException([$exception->getMessage()]);
        } else if (!$exception instanceof ApiExceptionInterface) {
            $exception = new CustomApiException($exception->getMessage());
        }

        throw $exception;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $payload = $event->getData();
        $payload['email'] = $user->getEmail();


        $event->setData($payload);
    }
}