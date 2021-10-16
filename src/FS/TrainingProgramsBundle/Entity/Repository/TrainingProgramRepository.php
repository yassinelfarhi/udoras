<?php

namespace FS\TrainingProgramsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\Vendor;

/**
 * TrainingProgramRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class TrainingProgramRepository extends EntityRepository
{
    /**
     * @param Customer $customer
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllByCustomerRaw(Customer $customer)
    {
        return $this->createQueryBuilder('trainingProgram')
            ->where('trainingProgram.customer = :customer')
            ->setParameters([
                'customer' => $customer
            ])
            ->orderBy('trainingProgram.title');
    }

    /**
     * @param Vendor $vendor
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllByVendorQuery(Vendor $vendor)
    {
        return $this->createQueryBuilder('trainingProgram')
            ->select('trainingProgram', 'request')
            ->leftJoin('trainingProgram.requests', 'request')
            ->leftJoin('request.vendor', 'vendor')
            ->where('vendor = :vendor')
            ->setParameter('vendor', $vendor)
            ->orderBy('trainingProgram.title');
    }

    /**
     * @param Employee $employee
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllByEmployeeQuery(Employee $employee)
    {
        return $this->createQueryBuilder('trainingProgram')
            ->select('trainingProgram', 'access')
            ->leftJoin('trainingProgram.accesses', 'access')
            ->leftJoin('access.employee', 'employee')
            ->where('employee = :employee')
            ->setParameter('employee', $employee)
            ->orderBy('trainingProgram.title');
    }
}