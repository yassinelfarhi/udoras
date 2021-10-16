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
 * Class GetNextSlideCommand
 * @package FS\TrainingProgramsBundle\Model\Command\TrainingState
 * @author <vladislav@fora-soft.com>
 */
class GetNextSlideCommand implements CommandInterface
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
        $slideId = $this->trainingState->getNextSlide();

        if($slideId === -1){
            $slide = new Slide();

            $slide->setSlideType(Slide::SLIDE_TYPE__NO_SLIDE);
            if ($hasLastTest = $this->trainingState->getTraining()->hasQuestions()) {
                $slide->setSlideType(Slide::SLIDE_TYPE_FINAL_QUESTION);
            }
            
            $slideCur = $slideRepository->find($this->trainingState->getCurrentSlide());
            $slide->setRealNum($slideCur->getRealNum() + 1);
            $this->trainingState->setCurrentSlide(-1);
            $this->trainingState->setEndTimer(null);
            $this->trainingState->setStartTimer(null);
            $em->persist($this->trainingState);
            $em->flush();
            return $slide;
        }

        $slide = $slideRepository->find($slideId);


        $nextSlide = $slideRepository->getSlideByTrainingProgramAndRealNum(
            $this->trainingState->getTraining(),
            (int)$slide->getRealNum() + 1
        );

        $this->trainingState->setCurrentSlide($slide->getId());

        if($nextSlide !== null){
            $this->trainingState->setNextSlide($nextSlide->getId());
        } else {
            $this->trainingState->setNextSlide(-1);
        }
        
        $this->trainingState->setEndTimer(null);
        $this->trainingState->setStartTimer(null);
        $this->trainingState->setTimeRemaining(0);

        $em->persist($this->trainingState);
        $em->flush();

        return $slide;
    }
}