<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\User;
use FS\UserBundle\Entity\Vendor;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161122151554 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var  ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function up(Schema $schema)
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $userRepository = $doctrine->getRepository('FSUserBundle:User');
        $deletedUsers = $userRepository->getAllDeleted();

        /** @var User $entity */
        foreach ($deletedUsers as $entity){
            if (strpos($entity->getUsername(), '_deleted_') === false) {
                $entity->setEmail($entity->getEmail() . '_deleted_' . time());
                //$entity->setLocked(true);
                //$entity->setEnabled(false);
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
            }
        }
        $entityManager->flush();
    }

    /**
     * @param Schema $schema
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function down(Schema $schema)
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $userRepository = $doctrine->getRepository('FSUserBundle:User');
        $deletedUsers = $userRepository->getAllDeleted();

        /** @var User $entity */
        foreach ($deletedUsers as $entity) {
            if (strpos($entity->getUsername(), '_deleted_') === true) {
                $userNamePos = strpos($entity->getUsername(), '_deleted_');
                $entity->setEmail(substr($entity->getEmail(), $userNamePos));
                $phonePos = strpos($entity->getPhone(), '_deleted_');
                $entity->setPhone(substr($entity->getPhone(), $phonePos));
                $entityManager->persist($entity);
            }
        }
        $entityManager->flush();

    }
}
