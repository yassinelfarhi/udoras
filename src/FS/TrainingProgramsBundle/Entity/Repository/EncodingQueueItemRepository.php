<?php

namespace FS\TrainingProgramsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use FS\TrainingProgramsBundle\Entity\EncodingQueueItem;

/**
 * EncodingQueueItemRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class EncodingQueueItemRepository extends EntityRepository
{

    public function getNotEncodedItem()
    {
        return $this->createQueryBuilder('item')
            ->where('item.encodingStatus = :notEncoded')
            ->setParameter('notEncoded', EncodingQueueItem::NOT_ENCODED)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
