<?php namespace MyAssistant\BudgetBundle\Entity;


use Carbon\Carbon;
use Doctrine\ORM\EntityRepository;

/**
 * IncomeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class IncomeRepository extends EntityRepository
{
    /**
     * Return total count
     *
     * @return int
     */
    public function getCount()
    {
        return (int)$this
            ->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param null $month
     * @param null $year
     *
     * @return int
     */
    public function getSum($year = null, $month = null)
    {
        $builder = $this->createQueryBuilder('t')
                        ->select('SUM(t.price)')
                        ->innerJoin('t.user', 'Income');

        if ($month && $year) {
            $date = Carbon::create($year, $month, 1, 0, 0, 0);
            $builder->where('t.date >= :startDate AND t.date < :endDate');
            $builder->setParameter(':startDate', $date->format('Y-m-d'));
            $builder->setParameter(':endDate', $date->addMonth()->format('Y-m-d'));
        }

        $result = $builder
            ->getQuery()
            ->getSingleScalarResult();

        if (!$result) {
            $result = 0;
        }

        return $result;
    }
}
