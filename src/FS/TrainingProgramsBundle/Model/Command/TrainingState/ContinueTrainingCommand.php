<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 02.11.2016
 * Time: 17:43
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingState;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;

/**
 * Class ContinueTrainingCommand
 * @package FS\TrainingProgramsBundle\Model\Command\TrainingState
 * @author <vladislav@fora-soft.com>
 */
class ContinueTrainingCommand implements CommandInterface
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
        $ts = $this->trainingState;
        $curSlide = $this->trainingState->getCurrentSlide();
        if (/*$curSlide !== -1 &&*/ $curSlide !== 0) {
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
                $time = $ts->getRemainTimePause();
                if($time > 0){
                    $end = new \DateTime();
                    $end->setTimestamp($dt->getTimestamp() + $time);
                    $ts->setEndTimer($end);
                    $ts->setStartTimer($dt);
                } else {
                    if($time === 0){
                        $time = -1;
                    }
                    $end = new \DateTime();
                    $ts->setRemainTimePause($time);
                    $end->setTimestamp($dt->getTimestamp() + $time);
                    $ts->setEndTimer($end);
                    $ts->setStartTimer($dt);
                }
            }
        }

        $ts->setPaused(false);

        $em->persist($ts);
        $em->flush();
        return (new GetCurrentSlideCommand($this->entityManager, $this->trainingState))
            ->execute();

    }
}