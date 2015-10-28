<?php namespace MyAssistant\AuthJwtBundle\DataFixtures\ORM;

use MyAssistant\AuthJwtBundle\Entity\EmailConfirmation;
use MyAssistant\AuthJwtBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->encoder = $this->container->get('security.password_encoder');

        $this->createUser('TestUser1', 'test_user1@gmail.com');
        $this->createUser('TestUser2', 'test_user2@gmail.com');
        $this->createUser('Admin', 'admin@gmail.com');

        $testUserWithConfirmation = $this->createUser('TestUserWithConfirmation',
            'test_user_with_confirmation@gmail.com');

        $this->createConfirmation($testUserWithConfirmation);
    }

    protected function createUser($username, $email, $password = '12345')
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email);

        $encoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($encoded);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    protected function createConfirmation(User $user)
    {
        $ec = (new EmailConfirmation())
            ->setEmail($user->getEmail())
            ->setConfirmationCode('123qwe');

        $user->setEmailConfirmation($ec);

        $this->manager->persist($user);
        $this->manager->flush();

        return $ec;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}