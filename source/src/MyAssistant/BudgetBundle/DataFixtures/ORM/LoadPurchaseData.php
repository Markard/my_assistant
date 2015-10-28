<?php namespace MyAssistant\BudgetBundle\DataFixtures\ORM;

use MyAssistant\AuthJwtBundle\DataFixtures\ORM\LoadUserData;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\BudgetBundle\Entity\Purchase;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\ORM\Doctrine\Populator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class LoadPurchaseData implements FixtureInterface, ContainerAwareInterface
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
        $user1 = $this->createUser('TestUserWithPurchases1', 'test_user_with_purchase1@gmail.com', '12345');
        $this->createMilkPurchase($user1);
        $this->generatePurchase(5, new \DateTime('2015-01-01'), 10, $user1);
        $this->generatePurchase(4, new \DateTime('2015-02-03'), 5, $user1);
        $this->generatePurchase(5, new \DateTime('2015-01-02'), 15, $user1);

        /** @var User $user2 */
        $user2 = $this->createUser('TestUserWithPurchases2', 'test_user_with_purchase2@gmail.com', '12345');
        $this->generatePurchase(20, new \DateTime('2015-01-01'), 10, $user2);
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

    private function generatePurchase($count, \DateTime $boughtAt, $price, User $user)
    {
        for ($i = 0; $i < $count; $i++) {
            $purchase = new Purchase();
            $purchase->setTitle('purchase')
                ->setAmount(10)
                ->setBoughtAt($boughtAt)
                ->setPrice($price)
                ->setUser($user);
            $this->manager->persist($purchase);
        }

        $this->manager->flush();
    }

    private function createMilkPurchase(User $user)
    {
        $purchase = new Purchase();
        $purchase->setTitle('Milk')
            ->setAmount(5)
            ->setPrice(100.99)
            ->setBoughtAt(new \DateTime('2015-01-01'))
            ->setUser($user);

        $this->manager->persist($purchase);
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