<?php

namespace FS\TrainingProgramsBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;


class LinksRepository extends EntityRepository
{
    public function getFindAllByTrainingProgramQuery(TrainingProgram $trainingProgram)
    {
        return $this->createQueryBuilder('link')
            ->distinct(true)
            ->where('link.trainingProgram = :trainingProgram')
            ->setParameters(['trainingProgram' => $trainingProgram])
            ->orderBy('link.id', 'ASC');
    }

    public function findByLink($link)
    {
        return $this->createQueryBuilder('link')
            ->where('link.link = :link')
            ->setParameters(['link' => $link])
            ->getQuery()->getOneOrNullResult();
    }
}