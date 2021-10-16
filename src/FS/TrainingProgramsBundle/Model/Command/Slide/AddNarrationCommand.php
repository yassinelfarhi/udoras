<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 14.10.2016
 * Time: 17:37
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Model\Command\Slide;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Model\Command\CommandInterface;
use FS\TrainingProgramsBundle\Util\FileManipulator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddAudioSlideCommand
 * @package FS\TrainingProgramsBundle\Model\Command\Slide
 * @author <vladislav@fora-soft.com>
 */
class AddNarrationCommand implements CommandInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var Slide */
    protected $slide;

    /** @var Request */
    protected $request;

    /** @var FileManipulator */
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
        $file = $this->request->files->get('file');
        $res = $this->fileManipulator->putSlideAudio($this->slide, $file);
        $this->slide->setExtraFields([
            'audio' => $res['path'],
            'time' => $res['attr']['time'],
            'encoding_id' => $res['attr']['encoding_id']
        ]);
        $this->fileManipulator->startEncoding();

        //$this->entityManager->persist($this->slide);
        //$this->entityManager->flush();

        return $this->slide;
    }
}