<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 18.10.2016
 * Time: 16:49
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingProgram;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;

class GetSlidesCommand implements CommandInterface
{

    /**
     * @var TrainingProgram
     */
    protected $trainingProgram;

    protected $entityManager;

    /**
     * GetSlidesCommand constructor.
     * @param TrainingProgram $trainingProgram
     * @param EntityManager $entityManager
     */
    public function __construct(TrainingProgram $trainingProgram, EntityManager $entityManager)
    {
        $this->trainingProgram = $trainingProgram;
        $this->entityManager = $entityManager;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $res = $this->trainingProgram
            ->getSlides()
            ->filter(function (Slide $slide) {
               return (
                   $slide->getState() === Slide::SLIDE_STATE__SAVED
                   && $slide->getMarker() != Slide::SLIDE_MARKER__DELETED
               );

            });
        $iterator = $res->getIterator();

        $iterator->uasort(function (Slide $a, Slide $b) {
            return ($a->getRealNum() < $b->getRealNum()) ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }
}