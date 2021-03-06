<?php

namespace FS\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Vendor;

/**
 * EmployeeRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class EmployeeRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllRaw()
    {
        return $this->createQueryBuilder('employee')
            ->distinct(true)
            ->where('employee.roles LIKE :roles')
            ->setParameter('roles', '%"ROLE_EMPLOYEE"%')
            ->orderBy('employee.fullName','ASC');
    }

    /**
     * @param Vendor $vendor
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindAllByVendorRaw(Vendor $vendor)
    {
        return $this->createQueryBuilder('employee')
            ->distinct(true)
            ->where('employee.vendor = :vendor')
            ->orderBy('employee.fullName', 'ASC')
            ->setParameters([
                'vendor' => $vendor
            ]);
    }

    /**
     * @param array $criteria
     * @return array
     */
    public function findByUniqueCriteria(array $criteria)
    {
        // would use findOneBy() but Symfony expects a Countable object
        return $this->_em->getRepository('FSUserBundle:Employee')->findBy($criteria);
    }

    /**
     * @param Customer $customer
     * @return array
     */
    public function findByCustomer(Customer $customer)
    {
        return $this->createQueryBuilder('employee')
            ->where('employee.enabled = true')
            ->leftJoin('employee.vendor', 'vendor')
            ->leftJoin('vendor.customer', 'customer')
            ->where('customer = :customer')
            ->setParameters(['customer' => $customer])
            ->getQuery()->getResult();
    }

    /**
     * find Employee, that have not access to training, by Customer and TrainingProgram
     *
     * @param Customer $customer
     * @param TrainingProgram $trainingProgram
     * @return array
     */
    public function findByCustomerWithoutTrainingProgram(Customer $customer, TrainingProgram $trainingProgram)
    {
        $employeesWithTrainingQuery = $this->getEntityManager()->createQuery(
            "SELECT emp FROM FSUserBundle:Employee emp ".
            "INNER JOIN emp.accesses access ".
            "WHERE access.trainingProgram = :trainingProgram"
        );

        $qb = $this->createQueryBuilder('employee');

        return $qb
            ->innerJoin('employee.vendor', 'vendor')
            ->innerJoin('vendor.customer', 'customer')
            ->where('customer = :customer')
            ->andWhere($qb->expr()->notIn('employee', $employeesWithTrainingQuery->getDQL()))
            ->setParameters([
                'customer' => $customer,
                'trainingProgram'=> $trainingProgram
            ])
            ->getQuery()->getResult();
    }

    /**
     * @param Vendor $vendor
     * @return array
     */
    public function findByVendor(Vendor $vendor)
    {
        return $this->createQueryBuilder('employee')
            ->where('employee.enabled = true')
            ->leftJoin('employee.vendor', 'vendor')
            ->where('vendor = :vendor')
            ->setParameters(['vendor' => $vendor])
            ->getQuery()->getResult();
    }

    /**
     * find Employee, that have not access to training, by Vendor and TrainingProgram
     *
     * @param Vendor $vendor
     * @param TrainingProgram $trainingProgram
     * @return array
     */
    public function findByVendorWithOutTrainingProgram(Vendor $vendor, TrainingProgram $trainingProgram)
    {
        $employeesWithTrainingQuery = $this->getEntityManager()->createQuery(
            "SELECT emp FROM FSUserBundle:Employee emp ".
            "INNER JOIN emp.accesses access ".
            "WHERE access.trainingProgram = :trainingProgram"
        );

        $qb = $this->createQueryBuilder('employee');

        return $qb
            ->where('employee.enabled = true')
            ->innerJoin('employee.vendor', 'vendor')
            ->where('vendor = :vendor')
            ->andWhere($qb->expr()->notIn('employee', $employeesWithTrainingQuery->getDQL()))
            ->setParameters([
                'vendor' => $vendor,
                'trainingProgram'=> $trainingProgram
            ])
            ->getQuery()->getResult();
    }

	public function addFilterConditions($query, QueryBuilder $qb, $searchVendor = true) {
		return $qb->leftJoin('employee.vendor', 'vendor')
		          ->andWhere(
			          $qb->expr()->orX(
				          'employee.fullName LIKE :query',
				          'employee.phone LIKE :query',
				          'employee.email LIKE :query',
				          $searchVendor ? 'vendor.fullName LIKE :query': '1 = 0'
			          )
		          )->setParameter('query', "%$query%");
	}

}
