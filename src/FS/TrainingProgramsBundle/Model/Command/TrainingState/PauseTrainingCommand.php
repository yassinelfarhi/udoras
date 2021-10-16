<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 01.11.2016
 * Time: 17:42
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingState;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;

/**
 * Class PauseTrainingCommand
 * @package FS\TrainingProgramsBundle\Model\Command\TrainingState
 * @author <vladislav@fora-soft.com>
 */
class PauseTrainingCommand implements CommandInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var EmployeeTrainingState */
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
     * @return mixed
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function execute()
    {
        $em = $this->entityManager;

        $this->trainingState->setPaused(true);
        $curSlide = $this->trainingState->getCurrentSlide();

        $tmpSlide = new Slide();
        $tmpSlide->setSlideType(Slide::SLIDE_TYPE__PAUSE);

        if (/*$curSlide !== -1 ||*/ $curSlide !== 0) {
            if($curSlide === -1){
                $slide  = (new GetCurrentSlideCommand($this->entityManager, $this->trainingState))
                    ->execute();
                $questions = $this->trainingState->getTraining()->getQuestionsSlides();
                $questions = $questions->toArray();
                $timeLimit = 0;

                /** @var Slide $question */
                foreach ($questions as $question){
                    $timeLimit += $question->getTimeLimit();
                }
                $slide->setTimeLimit($timeLimit);
            } else {
                $slide = $em->getRepository('FSTrainingProgramsBundle:Slide')->find($curSlide);
            }
            if ($slide && $slide->getTimeLimit() !== 0) {
                $dt = new \DateTime();
                $enDt = $this->trainingState->getEndTimer();
                $remainingSec = $enDt->getTimestamp() - $dt->getTimestamp();
                if($remainingSec === 0){
                    $remainingSec = -1;
                }

                $this->trainingState->setPauseStart($dt);
                $this->trainingState->setRemainTimePause($remainingSec);

                $em->persist($this->trainingState);
                $em->flush();
            }
        }

        return $tmpSlide;
    }
}