<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 22.11.2016
 * Time: 12:10
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\EventListener;


use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\User;
use FS\UserBundle\Entity\Vendor;

class SoftDeleteListener
{
    public function preSoftDelete(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        if ($entity instanceof User) {
            // change the unique columns
            if (strpos($entity->getUsername(), '_deleted_') === false) {
                $entity->setEmail($entity->getEmail() . '_deleted_' . time());
                $entity->setPhone($entity->getPhone(). '_deleted_' . time());

                $entityManager->persist($entity);
                $entityManager->flush();

                if ($entity instanceof Customer) {
                    $vendors = $entity->getVendors();
                    /** @var Vendor $vendor */
                    foreach ($vendors as $vendor) {
                        $vendor->setCustomer(null);
                        $entity->removeVendor($vendor);

                        $entityManager->persist($vendor);
                    }

                } else if ($entity instanceof Vendor) {
                    $employees = $entity->getEmployees();
                    $entity->setCustomer(null);
                    /** @var Employee $employee */
                    foreach ($employees as $employee) {
                        $employee->setVendor(null);

                        $entityManager->persist($employee);

                    }
                } else if ($entity instanceof Employee){
                    $entity->setVendor(null);
                }
                $entityManager->flush();
            }
        }
    }
}