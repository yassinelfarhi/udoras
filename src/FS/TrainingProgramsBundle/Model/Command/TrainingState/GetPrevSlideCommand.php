<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.10.2016
 * Time: 18:54
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingState;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;

/**
 * Class GetPrevSlideCommand
 * @package FS\TrainingProgramsBundle\Model\Command\TrainingState
 * @author <vladislav@fora-soft.com>
 */
class GetPrevSlideCommand implements CommandInterface
{

    /** @var EntityManager  */
    protected $entityManager;

    /** @var EmployeeTrainingState  */
    protected $trainingState;


    /**
     * GetCurrentSlideCommand constructor.
     * @param EntityManager $entityManager
     * @param EmployeeTrainingState $trainingState
     */
    public function __construct(
        EntityManager $entityManager,
        EmployeeTrainingState $trainingState
    )
    {
        $this->entityManager = $entityManager;
        $this->trainingState = $trainingState;
    }

    /**
     * @return Slide $slide
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function execute()
    {
        $em = $this->entityManager;

        $slideRepository = $em->getRepository('FSTrainingProgramsBundle:Slide');
        $slideId = $this->trainingState->getCurrentSlide();

        $slide = $slideRepository->find($slideId);

        $prevSlide = $slideRepository->getSlideByTrainingProgramAndRealNum(
            $this->trainingState->getTraining(),
            (int)$slide->getRealNum() - 1
        );

        if($prevSlide !== null){
            $this->trainingState->setCurrentSlide($prevSlide->getId());
        } else {
            $this->trainingState->setCurrentSlide(-1);
        }
        $this->trainingState->setNextSlide($slideId);
        
        $this->trainingState->setEndTimer(null);
        $this->trainingState->setStartTimer(null);
        $this->trainingState->setTimeRemaining(0);

        $em->persist($this->trainingState);
        $em->flush();

        return $prevSlide;
    }
}