<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 18.10.2016
 * Time: 12:53
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingProgram;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use Symfony\Component\HttpFoundation\Request;

class SaveSlidesCommand implements CommandInterface
{
    protected $trainingProgram;
    protected $entityManager;
    protected $request;

    public function __construct(EntityManager $entityManager, Request $request, TrainingProgram $trainingProgram)
    {
        $this->trainingProgram = $trainingProgram;
        $this->entityManager = $entityManager;
        $this->request = $request;
    }


    /**
     * @return mixed
     */
    public function execute()
    {
        $slidesData = $this->request->request->get('slides');
        $slides = $this->trainingProgram->getSlides();
        /** @var Slide $slide */
        foreach ($slides as $slide) {
            if($slide->getMarker() == Slide::SLIDE_MARKER__MARKED_FOR_DELETE){
                $slide->setMarker(Slide::SLIDE_MARKER__DELETED);
                continue;
            }
            foreach ($slidesData as $sd) {
                if ($slide->getId() == (int)$sd['id']) {

                    $slide->setRealNum((int)$sd['realNum']);
                    $slide->setSlideType($sd['type']);
                    $slide->setState(Slide::SLIDE_STATE__SAVED);
                    $slide->setTimeLimit($sd['timeLimit']);
                    if (array_key_exists('extraFields', $sd)) {
                        $slide->setExtraFields($sd['extraFields']);
                    }
                    if (array_key_exists('slideData', $sd)){
                        $slide->setSlideData($sd['slideData']);
                    }
                    
                    $this->entityManager->persist($slide);
                }
            }
        }

        $this->trainingProgram->setState(TrainingProgram::STATE_EDIT);

        $this->entityManager->persist($this->trainingProgram);
        $this->entityManager->flush();

        return $slides;
    }
}