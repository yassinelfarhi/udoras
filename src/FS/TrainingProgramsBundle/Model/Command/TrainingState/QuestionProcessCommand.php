<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.10.2016
 * Time: 19:02
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingState;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use Symfony\Component\HttpFoundation\Request;

class QuestionProcessCommand implements CommandInterface
{

    /** @var EntityManager */
    protected $entityManager;

    /** @var EmployeeTrainingState */
    protected $trainingState;

    /** @var Request  */
    protected $request;


    /**
     * GetCurrentSlideCommand constructor.
     * @param EntityManager $entityManager
     * @param Request $request
     * @param EmployeeTrainingState $trainingState
     */
    public function __construct(
        EntityManager $entityManager,
        Request $request,
        EmployeeTrainingState $trainingState
    )
    {
        $this->entityManager = $entityManager;
        $this->trainingState = $trainingState;
        $this->request = $request;
    }


    /**
     * @return Slide
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $em = $this->entityManager;

        $slideRepository = $em->getRepository('FSTrainingProgramsBundle:Slide');
        $slideId = $this->trainingState->getCurrentSlide();

        $slide = $slideRepository->find($slideId);
        $realNum = $slide->getRealNum();
        $answers = $this->request->request->get('answers',[]);
        $slideAnswers = $slide->getExtraFields()['answers'];
        $passed = true;
        for($i = 0; $i < 4; ++$i){
            if($slideAnswers[$i]['correct'] !== $answers[$i]['checked']){
                $passed = false;
                break;
            }
        }

        if($passed){
            return (new GetNextSlideCommand($this->entityManager, $this->trainingState))
                ->execute();
        } else {
            $this->trainingState->setNextSlide($slideId);
            $goto = $slide->getExtraFields()['id'];
            $this->trainingState->setCurrentSlide($goto);
            $slide = $slideRepository->find($goto);

            $this->trainingState->setEndTimer(null);
            $this->trainingState->setStartTimer(null);
            $this->trainingState->setTimeRemaining(0);
            $em->persist($this->trainingState);
            $em->flush();

            $slide = clone $slide;
            $slide->setRealNum($realNum);
            $slide->setProgram();
            if($slide->getSlideType() === Slide::SLIDE_TYPE__QUESTION){
                $slide->setSlideType(Slide::SLIDE_TYPE__ANSWERED_QUESTION);
            }

            $extra = $slide->getExtraFields();
            $extra['hidePrevButton'] = true;
            $slide->setExtraFields($extra);
            
            return $slide;
        }
    }
}