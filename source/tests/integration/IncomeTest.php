<?php


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\BudgetBundle\Entity\Income;
use MyAssistant\BudgetBundle\Entity\IncomeRepository;

class IncomeTest extends \Codeception\TestCase\Test
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var  EntityManager */
    protected $em;

    /** @var  IncomeRepository */
    protected $repository;

    protected function _before()
    {
        parent::_before();
        $this->em = $this->getModule('Doctrine2')->em;
        $this->repository = $this->em->getRepository('MyAssistant\BudgetBundle\Entity\Income');
    }

    public function testCreate()
    {
        $user = $this->getUser('TestUser1');
        $incomesBefore = $user->getIncomesPerMonth();
        $income = (new Income())
            ->setTitle('Test title')
            ->setPrice('10.5')
            ->setDate(new DateTime())
            ->setUser($user);

        $this->tester->persistEntity($income);
        $incomesAfter = $user->getIncomesPerMonth();

        /**
         * Assertions
         */
        $this->tester->seeInRepository('MyAssistant\BudgetBundle\Entity\Income', [
            'title' => 'Test title',
            'price' => '10.5'
        ]);

        $this->tester->expect("Income creation should increase user income per month counter.");
        $this->tester->assertEquals($incomesBefore + 1, $incomesAfter);
    }

    public function testDeleteIncomeShouldDecreasePurchaseCounterForUser()
    {
        $user = $this->getUser('TestUserWithIncome1');
        $incomesBefore = $user->getIncomesPerMonth();
        $income = $this->getIncome(1);

        $this->em->remove($income);
        $incomesAfter = $user->getIncomesPerMonth();

        /**
         * Assertions
         */
        $this->tester->assertEquals($incomesBefore - 1, $incomesAfter);
    }

    /**
     * @param $username
     *
     * @return null|User
     */
    private function getUser($username)
    {
        /** @var User $user */
        return $this->em->getRepository('MyAssistant\AuthJwtBundle\Entity\User')->findOneBy([
            'username' => $username
        ]);
    }

    /**
     * @param $id
     *
     * @return Income
     */
    private function getIncome($id)
    {
        /** @var User $user */
        return $this->em->getRepository('MyAssistant\BudgetBundle\Entity\Income')->findOneBy([
            'id' => $id
        ]);
    }

    public function testIncomeImplementUserAwareAnnotation()
    {
        $reader = new AnnotationReader();
        $userAware = $reader->getClassAnnotation(
            $this->em->getClassMetadata('MyAssistant\BudgetBundle\Entity\Income')->getReflectionClass(),
            'MyAssistant\AuthJwtBundle\Annotation\UserAware'
        );

        /**
         * Assertions
         */
        $this->assertInstanceOf('MyAssistant\AuthJwtBundle\Annotation\UserAware', $userAware);
        $this->assertEquals('user_id', $userAware->userFieldName);
    }

    public function testRepositoryCount()
    {
        $count = $this->repository->getCount();

        /**
         * Assertions
         */
        $this->tester->assertEquals(34, $count);
    }

    public function testRepositorySum()
    {
        $sum = $this->repository->getSum();

        $sumFromDb = $this->tester->grabFromDatabase('income', 'sum(price)');
        /**
         * Assertions
         */
        $this->tester->assertEquals($sumFromDb, $sum);
    }

    public function testRepositorySumForSpecialDate()
    {
        $sum = $this->repository->getSum(2015, 2);

        /**
         * Assertions
         */
        $this->tester->assertEquals(400, $sum);
    }
}