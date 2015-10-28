<?php namespace MyAssistant\AuthJwtBundle\Exception\Api;


use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\CoreBundle\Exception\Api\ApiExceptionInterface;
use Exception;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class NotConfirmEmailException extends AuthenticationException implements ApiExceptionInterface
{
    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return 'emailNotConfirmed';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalData()
    {
        return [
            'email' => $this->user->getEmail()
        ];
    }
}