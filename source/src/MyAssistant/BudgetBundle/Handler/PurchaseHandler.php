<?php namespace MyAssistant\BudgetBundle\Handler;


use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\CoreBundle\Handler\Handler;
use MyAssistant\BudgetBundle\Entity\Purchase;
use MyAssistant\BudgetBundle\Entity\PurchaseRepository;
use Carbon\Carbon;
use Doctrine\Common\Inflector\Inflector;

class PurchaseHandler extends Handler
{
    /**
     * {@inheritdoc}
     */
    public function post(array $parameters)
    {
        /** @var Purchase $entity */
        $entity = $this->createEntity();
        $entity = $this->processForm($entity, $parameters, 'POST');

        $user = $this->container->get('security.context')->getToken()->getUser();
        $entity->setUser($user);
        $this->saveEntity($entity);

        return $entity;
    }

    /**
     * @var PurchaseRepository
     */
    protected $repository;

    public function getSum(Carbon $date = null)
    {
        if ($date) {
            return $this->repository->getSum($date->year, $date->month);
        } else {
            return $this->repository->getSum();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all($limit = 10, $page = 1, $sort = null, $sortDirection = null, array $criteria = [])
    {
        return parent::all($limit, $page, $sort, $sortDirection, $criteria);
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

        if (isset($criteria['user_id'])) {
            $queryBuilder->where('p.user_id = :user_id')->setParameter(':user_id', $criteria['user_id']);
        }

        if (!empty($criteria['startDate']) && !empty($criteria['endDate'])) {
            $queryBuilder
                ->where('p.boughtAt >= :startDate')
                ->setParameter(':startDate', $criteria['startDate'])
                ->andWhere('p.boughtAt <= :endDate')
                ->setParameter(':endDate', $criteria['endDate']);
        }

        if (!empty($criteria['group_by_day'])) {
            $queryBuilder->groupBy('p.boughtAt');
        }

        $queryBuilder->orderBy('p.' . Inflector::camelize($sort), $sortDirection);

        return $queryBuilder->getQuery();
    }

    /**
     *  Return array of entities grouped by days.
     *  Result prepared by pagination serializer.
     * @see PaginationSerializerInterface::serialize()
     *
     * @param int $limit
     * @param int $page
     * @param null $sort
     * @param null $sortDirection
     * @param array $criteria
     *
     * @return mixed
     */
    public function allGropedByDays($limit = 10, $page = 1, $sort = null, $sortDirection = null, array $criteria = [])
    {
        $criteria['group_by_day'] = true;
        $query = $this->getQueryForAllItems($sort, $sortDirection, $criteria);
        $pagination = $this->paginator->paginate($query, $page, $limit, ['wrap-queries' => true]);

        $purchases = $this->paginationSerializer->serialize($pagination);

        $asDates = array_map(function (Purchase $purchase) {
            return $purchase->getBoughtAtAsDay();
        }, $purchases['items']);

        $purchases['items'] = [];
        /** @var Purchase $purchase */
        foreach ($this->repository->findAllForDays($asDates) as $purchase) {
            $id = $purchase->getBoughtAtAsDay();
            if (!isset($purchases['items'][$id])) {
                $purchases['items'][$id] = [];
            }
            $purchases['items'][$id][] = $purchase;
        }
        $purchases['items'] = array_values($purchases['items']);

        return $purchases;
    }
}