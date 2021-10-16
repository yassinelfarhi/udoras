<?php

namespace FS\TrainingProgramsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Employee;

/**
 * Class EmployeeTrainingStateRepository
 * @package FS\TrainingProgramsBundle\Entity\Repository
 * @author <vladislav@fora-soft.com>
 */
class EmployeeTrainingStateRepository extends EntityRepository
{

    /**
     * @param Employee $employee
     * @param TrainingProgram $trainingProgram
     * @return EmployeeTrainingState | null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTrainingStateByEmployeeAndProgram(Employee $employee, TrainingProgram $trainingProgram)
    {
        $qb = $this->createQueryBuilder('ts');

        $qb->select('ts', 'employee', 'training', 'access')
            ->leftJoin('ts.access', 'access')
            ->leftJoin('ts.training', 'training')
            ->leftJoin('access.employee', 'employee')
            ->where('employee = :employee')
            ->andWhere('training = :training')
            ->andWhere('ts.passedStatus = :status')
            ->setParameters([
                'employee' => $employee,
                'training' => $trainingProgram,
                'status' => EmployeeTrainingState::FINAL_STATUS_IN_PROGRESS
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Employee $employee
     * @param TrainingProgram $trainingProgram
     * @return EmployeeTrainingState | null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTrainingStateByEmployeeAndProgramFinished(Employee $employee, TrainingProgram $trainingProgram)
    {
        $qb = $this->createQueryBuilder('ts');

        $qb->select('ts', 'employee', 'training', 'access')
            ->leftJoin('ts.access', 'access')
            ->leftJoin('ts.training', 'training')
            ->leftJoin('access.employee', 'employee')
            ->where('employee = :employee')
            ->andWhere('training = :training')
            ->andWhere('ts.passedStatus != :status')
            ->setParameters([
                'employee' => $employee,
                'training' => $trainingProgram,
                'status' => EmployeeTrainingState::FINAL_STATUS_IN_PROGRESS
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateFinishedQueryBuilder()
    {
        $qb = $this->createQueryBuilder('ts');

        $qb->select('ts', 'training','access', 'employee', 'customer')
            ->leftJoin('ts.training', 'training')
            ->leftJoin('ts.access',' access')
            ->leftJoin('access.employee', 'employee')
            ->leftJoin('training.customer', 'customer')
            ->andWhere('ts.passedStatus = :status')
            ->orWhere('ts.passedStatus = :second_status')
            ->orderBy('training.title', 'ASC')
            ->setParameters([
                'status' => EmployeeTrainingState::FINAL_STATUS_PASSED,
                'second_status' => EmployeeTrainingState::FINAL_STATUS_FAILED
            ]);

        return $qb;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateFullyFinishedQueryBuilder()
    {
        $qb = $this->createQueryBuilder('ts');

        $qb->select('ts', 'training','access', 'employee', 'customer')
            ->leftJoin('ts.training', 'training')
            ->leftJoin('ts.access',' access')
            ->leftJoin('access.employee', 'employee')
            ->leftJoin('training.customer', 'customer')
            ->andWhere('ts.passedStatus = :status')
            ->orderBy('training.title', 'ASC')
            ->setParameters([
                'status' => EmployeeTrainingState::FINAL_STATUS_PASSED,
            ]);

        return $qb;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateFullyFinishedWithSoftDeletedQueryBuilder()
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        
        return $this->getAllTrainingStateFullyFinishedQueryBuilder();
    }

    /**
     * @param Employee $employee
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateFinishedByEmployeeQueryBuilder(Employee $employee)
    {
        $qb = $this->getAllTrainingStateFinishedQueryBuilder();

        $qb->andWhere('employee = :employee')
            ->setParameter('employee', $employee);

        return $qb;
    }

    /**
     * @param Employee $employee
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateFinishedByEmployeeWithSoftDeletedQueryBuilder(Employee $employee)
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        $qb = $this->getAllTrainingStateFinishedQueryBuilder();

        $qb->andWhere('employee = :employee')
            ->setParameter('employee', $employee);

        return $qb;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTrainingStateWithEmployeeAndCustomerById($id)
    {
        $qb = $this->createQueryBuilder('trainingState');

        $qb->select('trainingState', 'trainingProgram', 'access', 'employee', 'customer')
            ->innerJoin('trainingState.training', 'trainingProgram')
            ->innerJoin('trainingProgram.customer', 'customer')
            ->innerJoin('trainingState.access', 'access')
            ->innerJoin('access.employee', 'employee')
            ->where('trainingState.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function findTrainingStateWithEmployeeAndCustomerByIdWithSoftDeleted($id)
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');

        return $this->findTrainingStateWithEmployeeAndCustomerById($id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStates()
    {
        $qb = $this->createQueryBuilder('ts');

        $qb->select('ts', 'training','access', 'employee', 'customer', 'request', 'vendor')
            ->leftJoin('ts.training', 'training')
            ->leftJoin('ts.access',' access')
            ->leftJoin('access.employee', 'employee')
            ->leftJoin('access.request', 'request')
            ->leftJoin('request.vendor', 'vendor')
            ->leftJoin('training.customer', 'customer')
            ->orderBy('customer.company', 'ASC')
            ->addOrderBy('vendor.fullName', 'ASC')
            ->addOrderBy('employee.fullName', 'ASC')
            ->addOrderBy('training.title', 'ASC')
        ;

        return $qb;

    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStatesWithSoftDeleted()
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');

        return $this->getAllTrainingStates();
    }

    /**
     * @param Customer $customer
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateByCustomer(Customer $customer)
    {
        $qb = $this->getAllTrainingStates();
        $qb->where('customer = :customer')
            ->setParameter('customer', $customer);

        return $qb;
    }

    /**
     * @param Customer $customer
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllTrainingStateByCustomerWithSoftDeletes(Customer $customer)
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        $qb = $this->getAllTrainingStates();
        $qb->where('customer = :customer')
            ->setParameter('customer', $customer);

        return $qb;
    }
    
}
