<?php namespace MyAssistant\AuthJwtBundle\Security;


use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\AuthJwtBundle\Exception\Api\NotConfirmEmailException;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends BaseUserChecker
{
    public function checkPreAuth(UserInterface $user)
    {
        parent::checkPreAuth($user);
    }

    /**
     * @param UserInterface $user
     */
    public function checkPostAuth(UserInterface $user)
    {
        parent::checkPostAuth($user);

        if (!$user instanceof User) {
            return;
        }

        if ($user->getEmailConfirmation()) {
            throw new NotConfirmEmailException($user,
                'You have to confirm your email address. Confirmation code was sent to your email. '
                . 'If you did not receive confirmation code you can '
                . 'use Resend code link below.'
            );
        }
    }
}