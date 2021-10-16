<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 11.10.2016
 * Time: 14:53
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Model\Command\Slide\AddAudioSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\Slide\AddImageSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\Slide\AddNarrationCommand;
use FS\TrainingProgramsBundle\Model\Command\Slide\AddVideoSlideCommand;
use FS\TrainingProgramsBundle\Model\Command\Slide\RemoveSlideCommand;
use FS\TrainingProgramsBundle\Util\FileManipulator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SlideFactory
 * @package FS\TrainingProgramsBundle\Model
 * @author <vladislav@fora-soft.com>
 */
class SlideFactory implements FactoryInterface
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var Kernel
     */
    private $fileManipulator;

    /**
     * SlideFactory constructor.
     * @param EntityManager $entityManager
     * @param FormFactory $formFactory
     * @param RequestStack $requestStack
     * @param FileManipulator $fileManipulator
     */
    public function __construct(
        EntityManager $entityManager,
        FormFactory $formFactory,
        RequestStack $requestStack,
        FileManipulator $fileManipulator
    )
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->fileManipulator = $fileManipulator;
    }

    /**
     * @param $command
     * @param $object
     * @return mixed
     */
    public function factory($command, $object)
    {
        if ($command == 'remove') {
            return new RemoveSlideCommand($this->entityManager, $object);
        } elseif ($command == 'add_text') {
            return;
        } elseif ($command == 'add_image'){
            return new AddImageSlideCommand(
                $this->entityManager,
                $object,
                $this->request,
                $this->fileManipulator
            );
        } elseif ($command == "add_video"){
            return new AddVideoSlideCommand(
                $this->entityManager,
                $object,
                $this->request,
                $this->fileManipulator
            );
        } elseif ($command == "add_audio"){
            return new AddAudioSlideCommand(
                $this->entityManager,
                $object,
                $this->request,
                $this->fileManipulator
            );
        }elseif ($command == "add_narration"){
            return new AddNarrationCommand(
                $this->entityManager,
                $object,
                $this->request,
                $this->fileManipulator
            );
        }

        throw new \RuntimeException('Cannot find command ' . $command);
    }
}