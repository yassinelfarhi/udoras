<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 13.10.2016
 * Time: 18:29
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\Slide;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use FS\TrainingProgramsBundle\Util\FileManipulator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class AddVideoSlideCommand implements CommandInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var Slide */
    protected $slide;

    /** @var Request  */
    protected $request;

    /** @var FileManipulator  */
    protected $fileManipulator;

    /**
     * RemoveSlideCommand constructor.
     * @param EntityManager $entityManager
     * @param Slide $slide
     * @param Request $request
     * @param FileManipulator $fileManipulator
     */
    public function __construct(EntityManager $entityManager, Slide $slide, Request $request, FileManipulator $fileManipulator)
    {
        $this->entityManager = $entityManager;
        $this->slide = $slide;
        $this->request = $request;
        $this->fileManipulator = $fileManipulator;
    }

    public function execute()
    {
        /** @var UploadedFile $file */
        $file  = $this->request->files->get('file');
        $res = $this->fileManipulator->putSlideVideo($this->slide, $file);
        $this->slide->setSlideType(Slide::SLIDE_TYPE__VIDEO);
        $this->slide->setSlideData($res['path']);
        $this->slide->setExtraFields($res['attr']);
        $this->fileManipulator->startEncoding();

        //$this->entityManager->persist($this->slide);
        //$this->entityManager->flush();

        return $this->slide;
    }
}