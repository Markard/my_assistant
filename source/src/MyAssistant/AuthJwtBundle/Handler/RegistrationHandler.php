<?php namespace MyAssistant\AuthJwtBundle\Handler;


use MyAssistant\AuthJwtBundle\Entity\EmailConfirmation;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\CoreBundle\Handler\Handler;

class RegistrationHandler extends Handler
{
    public function post(array $parameters)
    {
        /** @var User $user */
        $user = $this->createEntity();

        $user = $this->processForm($user, $parameters, 'POST');

        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($encoded);

        $emailConfirmation = (new EmailConfirmation())->generateConfirmationCode();
        $user->setEmailConfirmation($emailConfirmation);

        $this->saveEntity($user);

        return $user;
    }

}