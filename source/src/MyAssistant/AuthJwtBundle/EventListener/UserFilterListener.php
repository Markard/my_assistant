<?php  namespace MyAssistant\AuthJwtBundle\EventListener; 


use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\AuthJwtBundle\Filters\UserFilter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserFilterListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage, Reader $reader)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->reader = $reader;
    }

    public function onKernelRequest()
    {
        if ($user = $this->getUser()) {
            /** @var UserFilter $filter */
            $filter = $this->em->getFilters()->enable('user_filter');
            $filter->setParameter('id', $user->getId());
            $filter->setAnnotationReader($this->reader);
        }
    }

    /**
     * @return User|null
     */
    private function getUser()
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();

        if (!($user instanceof UserInterface)) {
            return null;
        }

        return $user;
    }
}