<?php namespace MyAssistant\BudgetBundle\DataFixtures\ORM;

use MyAssistant\AuthJwtBundle\DataFixtures\ORM\LoadUserData;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\BudgetBundle\Entity\Income;
use MyAssistant\BudgetBundle\Entity\Purchase;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\ORM\Doctrine\Populator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class LoadIncomeData implements FixtureInterface, ContainerAwareInterface
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

        /** @var User $user1 */
        $user1 = $this->createUser('TestUserWithIncome1', 'test_user_with_income1@gmail.com', '12345');
        $this->generateIncome(5, new \DateTime('2015-01-01'), $user1, '10.5');
        $this->generateIncome(4, new \DateTime('2015-02-01'), $user1, '100');
        $this->generateIncome(5, new \DateTime('2015-03-01'), $user1, '105.8');

        /** @var User $user2 */
        $user2 = $this->createUser('TestUserWithIncome2', 'test_user_with_income2@gmail.com', '12345');
        $this->generateIncome(20, new \DateTime('2015-01-01'), $user2, '401.24');
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

    private function generateIncome($count, \DateTime $date, User $user, $price = null)
    {
        if (!$price) {
            $price = $this->faker->randomFloat(2, 1, 1000);
        }

        for ($i = 0; $i < $count; $i++) {
            $income = new Income();
            $income->setTitle('salary')
                   ->setPrice($price)
                   ->setDate($date)
                   ->setUser($user);
            $this->manager->persist($income);
        }

        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}