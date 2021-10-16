<?php

namespace FS\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    /**
     * @param array $criteria
     * @return array
     */
    public function findByUniqueCriteria(array $criteria)
    {
        // would use findOneBy() but Symfony expects a Countable object
        return $this->_em->getRepository('FSUserBundle:User')->findBy($criteria);
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getAllDeleted()
    {

        $qb = $this->createQueryBuilder('u');
        $qb->getEntityManager()->getFilters()->disable('softdeleteable');

        $qb->where($qb->expr()->isNotNull('u.deletedAt'));
        $res = $qb->getQuery()->getResult();

        $qb->getEntityManager()->getFilters()->enable('softdeleteable');
        return $res;
    }
}