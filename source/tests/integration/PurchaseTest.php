<?php


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\BudgetBundle\Entity\Purchase;
use MyAssistant\BudgetBundle\Entity\PurchaseRepository;

class PurchaseTest extends \Codeception\TestCase\Test
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var  EntityManager */
    protected $em;

    /** @var  PurchaseRepository */
    protected $repository;

    protected function _before()
    {
        parent::_before();
        $this->em = $this->getModule('Doctrine2')->em;
        $this->repository = $this->em->getRepository('MyAssistant\BudgetBundle\Entity\Purchase');
    }

    public function testCreate()
    {
        $user = $this->getUser('TestUser1');
        $purchasesBefore = $user->getPurchasesPerDay();
        $purchase = (new Purchase())
            ->setTitle('Test title')
            ->setAmount(10)
            ->setPrice(10.5)
            ->setBoughtAt(new DateTime())
            ->setUser($user);

        $this->tester->persistEntity($purchase);
        $purchasesAfter = $user->getPurchasesPerDay();

        /**
         * Assertions
         */
        $this->tester->seeInRepository('MyAssistant\BudgetBundle\Entity\Purchase', [
            'title' => 'Test title',
            'amount' => 10,
            'price' => 10.5
        ]);

        $this->tester->expect("Purchase creation should increase user purchase per day counter.");
        $this->tester->assertEquals($purchasesBefore + 1, $purchasesAfter);
    }

    public function testDeletePurchaseShouldDecreasePurchaseCounterForUser()
    {
        $user = $this->getUser('TestUserWithPurchases1');
        $purchasesBefore = $user->getPurchasesPerDay();
        $purchase = $this->getPurchase(1);

        $this->em->remove($purchase);
        $purchasesAfter = $user->getPurchasesPerDay();

        /**
         * Assertions
         */
        $this->tester->assertEquals($purchasesBefore - 1, $purchasesAfter);
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
     * @return Purchase
     */
    private function getPurchase($id)
    {
        /** @var User $user */
        return $this->em->getRepository('MyAssistant\BudgetBundle\Entity\Purchase')->findOneBy([
            'id' => $id
        ]);
    }

    public function testPurchaseImplementUserAwareAnnotation()
    {
        $reader = new AnnotationReader();
        $userAware = $reader->getClassAnnotation(
            $this->em->getClassMetadata('MyAssistant\BudgetBundle\Entity\Purchase')->getReflectionClass(),
            'MyAssistant\AuthJwtBundle\Annotation\UserAware'
        );

        /**
         * Assertions
         */
        $this->assertInstanceOf('MyAssistant\AuthJwtBundle\Annotation\UserAware', $userAware);
        $this->assertEquals('user_id', $userAware->userFieldName);
    }

    public function testRepositoryFindAllByDaysForSpecialUser()
    {
        $purchases = $this->repository->findAllForDays(['2015-01-02', '2015-02-03']);

        /**
         * Assertions
         */
        $this->tester->assertEquals(9, count($purchases));
    }

    public function testRepositoryCount()
    {
        $count = $this->repository->getCount();

        /**
         * Assertions
         */
        $this->tester->assertEquals(35, $count);
    }

    public function testRepositorySum()
    {
        $sum = $this->repository->getSum();

        $sumFromDb = $this->tester->grabFromDatabase('purchase', 'sum(price)');
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
        $this->tester->assertEquals(20, $sum);
    }
}