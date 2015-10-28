<?php namespace MyAssistant\BudgetBundle\Handler;


use Carbon\Carbon;
use Doctrine\Common\Inflector\Inflector;
use MyAssistant\BudgetBundle\Entity\Income;
use MyAssistant\BudgetBundle\Entity\Purchase;
use MyAssistant\BudgetBundle\Entity\PurchaseRepository;
use MyAssistant\CoreBundle\Handler\Handler;

class IncomeHandler extends Handler
{
    /**
     * @var PurchaseRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    public function post(array $parameters)
    {
        /** @var Income $entity */
        $entity = $this->createEntity();
        $entity = $this->processForm($entity, $parameters, 'POST');

        $user = $this->container->get('security.context')->getToken()->getUser();
        $entity->setUser($user);
        $this->saveEntity($entity);

        return $entity;
    }

    public function getSum(Carbon $date = null)
    {
        if ($date) {
            return $this->repository->getSum($date->year, $date->month);
        } else {
            return $this->repository->getSum();
        }
    }

    /**
     * @param $sort
     * @param $sortDirection
     * @param array $criteria
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getQueryForAllItems($sort, $sortDirection, array $criteria)
    {
        $queryBuilder = $this->repository->createQueryBuilder('p')
                                         ->select('p');

        if (!empty($criteria['startDate']) && !empty($criteria['endDate'])) {
            $queryBuilder
                ->where('p.date >= :startDate')
                ->setParameter(':startDate', $criteria['startDate'])
                ->andWhere('p.date <= :endDate')
                ->setParameter(':endDate', $criteria['endDate']);
        }

        $queryBuilder->orderBy('p.' . Inflector::camelize($sort), $sortDirection);

        return $queryBuilder->getQuery();
    }
}