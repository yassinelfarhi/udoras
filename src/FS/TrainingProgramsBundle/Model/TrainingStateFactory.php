<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.10.2016
 * Time: 18:53
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\ContinueTrainingCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\FinalQuestionCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\GetCurrentSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\GetNextSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\GetPrevSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\PauseTrainingCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingState\QuestionProcessCommand;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Class TrainingStateFactory
 * @package FS\TrainingProgramsBundle\Model
 * @author <vladislav@fora-soft.com>
 */
class TrainingStateFactory implements FactoryInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var null|\Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var \Twig_Environment */
    protected $templating;

    /**
     * TrainingStateFactory constructor.
     * @param EntityManager $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManager $entityManager,
        RequestStack $requestStack
    )
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param $command
     * @param $object
     * @return CommandInterface
     * @throws \RuntimeException
     */
    public function factory($command, $object)
    {
        if ($command === 'current_slide') {
            return new GetCurrentSlideCommand($this->entityManager, $object);
        } else if ($command === 'next_slide') {
            return new GetNextSlideCommand($this->entityManager, $object);
        } else if ($command === 'prev_slide') {
            return new GetPrevSlideCommand($this->entityManager, $object);
        } else if ($command === 'question') {
            return new QuestionProcessCommand($this->entityManager, $this->request, $object);
        } else if ($command === 'final_question') {
            return new FinalQuestionCommand($this->entityManager, $this->request, $object);
        } else if ($command === 'pause') {
            return new PauseTrainingCommand($this->entityManager, $object);
        } else if ($command === 'continue') {
            return new ContinueTrainingCommand($this->entityManager, $object);
        }

        throw new \RuntimeException('Cannot find command ' . $command);
    }
}