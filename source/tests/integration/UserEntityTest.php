<?php


use MyAssistant\AuthJwtBundle\Entity\EmailConfirmation;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\AuthJwtBundle\Entity\UserRepository;
use MyAssistant\BudgetBundle\Entity\Purchase;
use MyAssistant\BudgetBundle\Entity\Income;

class UserEntityTest extends \Codeception\TestCase\Test
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var \Doctrine\ORM\EntityManager $em */
    protected $em;

    /** @var  UserRepository */
    protected $userRepository;

    protected function _before()
    {
        parent::_before();
        $this->em = $this->getModule('Doctrine2')->em;
        $this->userRepository = $this->em->getRepository('MyAssistant\AuthJwtBundle\Entity\User');
    }

    public function testCreate()
    {
        $user = (new User())
            ->setUsername('test')
            ->setEmail('test@gmail.com')
            ->setPassword('12345')
            ->setTimezone('UTC');

        $this->tester->persistEntity($user);

        $this->tester->seeInRepository('MyAssistant\AuthJwtBundle\Entity\User', ['username' => 'test']);
    }

    public function testUpdate()
    {
        /** @var User $userFromDb */
        $userFromDb = $this->userRepository->findOneBy(['username' => 'TestUser1']);
        $userFromDb->setUsername('newTest');

        $this->tester->persistEntity($userFromDb);

        $this->tester->seeInRepository('MyAssistant\AuthJwtBundle\Entity\User', ['username' => 'newTest']);
    }

    public function testCreateEmailConfirmationForUser()
    {
        /** @var User $userFromDb */
        $userFromDb = $this->userRepository->findOneBy(['username' => 'TestUser1']);

        $ec = (new EmailConfirmation())->generateConfirmationCode();
        $userFromDb->setEmailConfirmation($ec);

        $this->tester->persistEntity($userFromDb);

        $this->tester->seeInRepository('MyAssistant\AuthJwtBundle\Entity\EmailConfirmation', [
            'email' => $userFromDb->getEmail(),
            'user' => $userFromDb
        ]);
    }

    public function testRemoveEmailConfirmationFromUser()
    {
        /** @var User $userFromDb */
        $userFromDb = $this->userRepository->findOneBy(['username' => 'TestUserWithConfirmation']);
        $userFromDb->removeEmailConfirmation();

        $this->tester->persistEntity($userFromDb);

        $this->tester->dontseeInRepository('MyAssistant\AuthJwtBundle\Entity\EmailConfirmation', [
            'email' => $userFromDb->getEmail(),
            'user' => $userFromDb
        ]);
    }

    public function testAddPurchase()
    {
        /** @var User $userFromDb */
        $userFromDb = $this->userRepository->findOneBy(['username' => 'TestUser1']);

        $purchase = (new Purchase())
            ->setTitle('testNewPurchase')
            ->setPrice('11.1')
            ->setAmount(1)
            ->setBoughtAt(new \DateTime());

        $userFromDb->addPurchase($purchase);

        $this->tester->persistEntity($userFromDb);

        $this->tester->seeInRepository('MyAssistant\BudgetBundle\Entity\Purchase', [
            'title' => 'testNewPurchase',
            'user' => $userFromDb
        ]);
    }

    public function testAddIncome()
    {
        /** @var User $userFromDb */
        $userFromDb = $this->userRepository->findOneBy(['username' => 'TestUser1']);

        $income = (new Income())
            ->setTitle('testNewIncome')
            ->setPrice('11.21')
            ->setDate(new \DateTime());

        $userFromDb->addIncome($income);

        $this->tester->persistEntity($userFromDb);

        $this->tester->seeInRepository('MyAssistant\BudgetBundle\Entity\Income', [
            'title' => 'testNewIncome',
            'user' => $userFromDb
        ]);
    }

    public function testResetAllPurchaseCounters()
    {
        $this->userRepository->resetPurchaseCounters();

        /**
         * Assertions
         */
        $this->tester->seeInRepository('MyAssistant\AuthJwtBundle\Entity\User', [
            'username' => 'TestUserWithPurchases1',
            'purchasesPerDay' => 0
        ]);
    }

    public function testResetAllIncomeCounters()
    {
        $this->userRepository->resetIncomeCounters();

        /**
         * Assertions
         */
        $this->tester->seeInRepository('MyAssistant\AuthJwtBundle\Entity\User', [
            'username' => 'TestUserWithIncome1',
            'incomesPerMonth' => 0
        ]);
    }
}