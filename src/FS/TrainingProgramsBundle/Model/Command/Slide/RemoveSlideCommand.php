<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 11.10.2016
 * Time: 15:08
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\Slide;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;

class RemoveSlideCommand implements CommandInterface
{

    /** @var EntityManager */
    protected $entityManager;

    /** @var Slide */
    protected $slide;

    /**
     * RemoveSlideCommand constructor.
     * @param EntityManager $entityManager
     * @param Slide $slide
     */
    public function __construct(EntityManager $entityManager, Slide $slide)
    {
        $this->entityManager = $entityManager;
        $this->slide = $slide;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        try {
            /** @var TrainingProgram $trainingProgram */
            //$trainingProgram = $this->slide->getProgram();
            //$trainingProgram->removeSlide($this->slide);
            $this->slide->setMarker(Slide::SLIDE_MARKER__MARKED_FOR_DELETE);
            $this->entityManager->persist($this->slide);
            //$this->entityManager->remove($this->slide);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->slide;
        }


        return $this->slide;
    }
}