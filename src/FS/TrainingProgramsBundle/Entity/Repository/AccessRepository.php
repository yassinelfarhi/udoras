<?php

namespace FS\TrainingProgramsBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use FS\UserBundle\Entity\Employee;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;


class AccessRepository extends  EntityRepository
{
    /**
     * @param Employee $employee
     * @param TrainingProgram $trainingProgram
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByEmployeeAndTrainingProgram(Employee $employee, TrainingProgram $trainingProgram)
    {
        return $this->createQueryBuilder('access')
            ->leftJoin('access.employee', 'employee')
            ->leftJoin('access.trainingProgram', 'trainingProgram')
            ->where('employee = :employee')
            ->andWhere('trainingProgram = :trainingProgram')
            ->setParameters([
                'employee' => $employee,
                'trainingProgram' => $trainingProgram
            ])
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Employee $employee
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllByEmployeeRaw(Employee $employee)
    {
        return $this->createQueryBuilder('access')
            ->leftJoin('access.employee', 'employee')
            ->where('employee = :employee')
            ->setParameter('employee', $employee);
    }

    /**
     * @param Employee $employee
     * @return \Doctrine\ORM\Query
     */
    public function getFindAllByEmployeeWithTrainingStateQuery(Employee $employee)
    {
        $qb = $this->createQueryBuilder('access');

        $qb->select('access', 'trainingState', 'trainingProgram')
            ->innerJoin('access.employee', 'employee')
            ->innerJoin('access.trainingProgram', 'trainingProgram')
            ->innerJoin('access.trainingState', 'trainingState')
            ->where('employee = :employee')
            ->setParameter('employee', $employee);

        return $qb->getQuery();

    }
}