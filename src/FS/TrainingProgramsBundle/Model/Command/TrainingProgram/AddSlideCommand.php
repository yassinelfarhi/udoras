<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 07.10.2016
 * Time: 15:01
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\TrainingProgram;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;

/**
 * Class AddSlideCommand
 * @package FS\TrainingProgramsBundle\Model\Command\TrainingProgram
 * @author <vladislav@fora-soft.com>
 */
class AddSlideCommand implements CommandInterface
{
    protected $trainingProgram;
    protected $entityManager;

    /**
     * AddSlideCommand constructor.
     * @param EntityManager $entityManager
     * @param TrainingProgram $trainingProgram
     */
    public function __construct(EntityManager $entityManager, TrainingProgram $trainingProgram)
    {
        $this->trainingProgram = $trainingProgram;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Slide
     */
    public function execute()
    {
        $slide = new Slide();
        $this->trainingProgram->addSlide($slide);
        $this->entityManager->persist($slide);
        $this->entityManager->flush($slide);

        return $slide;
    }
}