<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 28.10.2016
 * Time: 17:17
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingState;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FinalQuestionCommand
 * @package FS\TrainingProgramsBundle\Model\Command\TrainingState
 * @author <vladislav@fora-soft.com>
 */
class FinalQuestionCommand implements CommandInterface
{

    /** @var EntityManager */
    protected $entityManager;

    /** @var EmployeeTrainingState */
    protected $trainingState;

    /** @var Request */
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
     * @return mixed
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $em = $this->entityManager;
        /** @var array $answers */
        $answers = $this->request->request->get('answers', []);
        $tp = $this->trainingState->getTraining();
        /** @var ArrayCollection $questions */
        $questions = $tp->getQuestionsSlides();
        $finalRatioForOne = 100 / $questions->count();
        $points = 0;
        foreach ($answers as $id => $value) {
            /** @var Slide $question */
            foreach ($questions as $question) {
                if ($question->getId() === $id) {
                    if ($this->checkQuestion($question->getExtraFields()['answers'], $value)) {
                        $points += $finalRatioForOne;
                    }
                }
            }
        }

        $this->trainingState->setEndTime(new \DateTime());
        $this->trainingState->setTimeOffset($this->request->request->get('offset', 0));
        $this->trainingState->setRatio($points);

        if ($tp->getPassing() <= $points) {
            $this->trainingState->setPassedStatus(EmployeeTrainingState::FINAL_STATUS_PASSED);
            $this->trainingState->generateCertificateId();
            $this->trainingState->fillValidUntil();
        } else {
            $this->trainingState->setPassedStatus(EmployeeTrainingState::FINAL_STATUS_FAILED);
        }

        $em->persist($this->trainingState);
        $em->flush();

        $slide = new Slide();
        $slide->setSlideType(Slide::SLIDE_TYPE__REDIRECT);

        return $slide;
    }

    /**
     * @param array $questions
     * @param array $answers
     * @return bool
     */
    private function checkQuestion($questions, $answers)
    {
        for($i = 0; $i < 4; ++$i){
            if($questions[$i]['correct'] !== $answers[$i]['checked']){
                return false;
            }
        }
        return true;
    }
}