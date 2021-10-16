<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 07.10.2016
 * Time: 15:12
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use FS\TrainingProgramsBundle\Model\Command\TrainingProgram\AddSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingProgram\GetSlidesCommand;
use FS\TrainingProgramsBundle\Model\Command\TrainingProgram\SaveSlidesCommand;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TrainingProgramFactory
 * @package FS\TrainingProgramsBundle\Model
 * @author <vladislav@fora-soft.com>
 */
class TrainingProgramFactory implements FactoryInterface
{
    private $entityManager;
    private $request;

    /**
     * TrainingProgramFactory constructor.
     * @param EntityManager $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManager $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param $command
     * @param $object
     * @return CommandInterface
     */
    public function factory($command, $object)
    {
        if ($command == 'add_slide') {
            return new AddSlideCommand($this->entityManager, $object);
        } elseif ($command == 'save_slides') {
            return new SaveSlidesCommand($this->entityManager, $this->request, $object);
        } else if($command == 'slides'){
            return new GetSlidesCommand($object, $this->entityManager);
        }

        throw new \RuntimeException('Cannot find command ' . $command);
    }
}