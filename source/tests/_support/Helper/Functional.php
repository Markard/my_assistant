<?php namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\TestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Functional extends \Codeception\Module
{
    /**
     * @return \Swift_Message
     * @throws \Codeception\Exception\ModuleException
     */
    public function getEmailMessage()
    {
        $profiler = $this->getProfiler();
        $mailCollector = $profiler->getCollector('swiftmailer');

        // Check that an email was sent
        $this->assertEquals(1, $mailCollector->getMessageCount(), 'Request should sent at least one email.');

        $collectedMessages = $mailCollector->getMessages();

        /** @var \Swift_Message $message */

        return $collectedMessages[0];
    }

    /**
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    protected function getProfiler()
    {
        /** @var Kernel $kernel */
        $kernel = $this->getModule('Symfony2')->kernel;
        /** @var Client $client */
        $client = $this->getModule('Symfony2')->client;
        if (!$kernel->getContainer()->has('profiler')) {
            return null;
        }
        $profiler = $kernel->getContainer()->get('profiler');

        return $profiler->loadProfileFromResponse($client->getResponse());
    }

    public function updateEmailConfirmationCreatedAt(\Carbon\Carbon $createdAt)
    {
        $userId = $this->getModule('\Helper\CustomDb')->grabFromDatabase('user', 'id', ['username' => 'TestUser1']);
        $this->getModule('\Helper\CustomDb')->haveInDatabase('email_confirmation', [
            'email' => 'test_user1@gmail.com',
            'confirmation_code' => '123qwe',
            'created_at' => $createdAt,
            'user_id' => $userId
        ], false);
    }

    public function authenticated($username)
    {
        /** @var Container $container */
        $container = $this->getModule('Symfony2')->kernel->getContainer();
        /** @var Client $client */
        $client = $this->getModule('Symfony2')->client;
        /** @var EntityManager $em */
        $em = $this->getModule('Doctrine2')->em;

        $userRepository = $em->getRepository('MyAssistant\AuthJwtBundle\Entity\User');
        $user = $userRepository->findOneBy(['username' => $username]);
        $jwt = $container->get('lexik_jwt_authentication.jwt_manager')->create($user);
        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $jwt);
    }

    public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->getModule('Symfony2')->kernel->getContainer()
                                                   ->get('router')
                                                   ->generate($route, $parameters, $referenceType);
    }

    /**
     * @return EntityManager
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function getEm()
    {
        return $this->getModule('Doctrine2')->em;
    }
}
