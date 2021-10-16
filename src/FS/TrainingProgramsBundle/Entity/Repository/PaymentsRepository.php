<?php

namespace FS\TrainingProgramsBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;


class PaymentsRepository extends EntityRepository
{
    /**
     *  Find all Payments with User and TrainingProgram
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllWithDeletedOrderedByDateRaw()
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        $paymentsQueryBuilder = $this->createQueryBuilder('payment');

        $paymentsQueryBuilder
            ->leftJoin('payment.user', 'user')
            ->leftJoin('payment.trainingProgram', 'trainingProgram')
            ->orderBy('payment.date', 'DESC');

        return $paymentsQueryBuilder;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPaymentsGroupedByTrainingsRaw()
    {
        $qb = $this->createQueryBuilder('payment');

        $qb->select('payment', 'customer', 'trainingProgram',  'SUM(payment.totalPrice)')
            ->innerJoin('payment.trainingProgram', 'trainingProgram')
            ->innerJoin('trainingProgram.customer', 'customer')
            ->groupBy('trainingProgram.title')
            ->addGroupBy('customer.company')
            ->orderBy('customer.company', 'ASC')
            ->addOrderBy('trainingProgram.title', 'ASC');

        return $qb;
    }

    public function getPaymentsGroupedByTrainingsWithSoftDeletedRaw()
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');

        return $this->getPaymentsGroupedByTrainingsRaw();
    }
}