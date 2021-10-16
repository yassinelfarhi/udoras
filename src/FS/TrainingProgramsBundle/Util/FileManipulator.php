<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 12.10.2016
 * Time: 16:05
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Util;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EncodingQueueItem;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;

class FileManipulator
{
    private $filesystem;

    private $fileStorage;

    private $entityManager;


    public function __construct($kernelDir, EntityManager $em)
    {
        $this->filesystem = new Filesystem();

        $this->fileStorage = $kernelDir . '/../web/media';
        $this->webDir = $kernelDir . '/../web';

        if (!$this->filesystem->exists($this->fileStorage)) {
            $this->filesystem->mkdir($this->fileStorage, 0777);
        }
        $this->entityManager = $em;
    }

    public function putSlideImage(Slide $slide, UploadedFile $file)
    {

        /** @var TrainingProgram $tp */
        $tp = $slide->getProgram();

        $path = $this->fileStorage . '/' . $tp->getId() . '/img';
        $name = md5_file($file->getRealPath()) . '.' . $file->guessExtension();
        $file->move($path, $name);
        $imgNormPath = '/media/' . $slide->getProgram()->getId() . '/img/' . $name;
        list($width, $height, $type, $attr) = getimagesize($path . '/' . $name);


        return [
            'path' => $imgNormPath,
            'attr' => [
                'width' => $width,
                'height' => $height,
                'top' => 0
            ]
        ];
    }

    public function putSlideVideo(Slide $slide, UploadedFile $file)
    {
        /** @var TrainingProgram $tp */
        $tp = $slide->getProgram();

        $path = $this->fileStorage . '/' . $tp->getId() . '/video';
        $md5 = md5_file($file->getRealPath());
        $name = $md5 . "_orig." . $file->guessExtension();
        $file->move($path, $name);
        $imgNormPath = '/media/' . $tp->getId() . '/video/' . $name;

        $data = $this->getMediaInfo($this->webDir . $imgNormPath);
        $playtime = 0;
        if ($data) {
            $playtime = (int)$data['format']['duration'];
        }

        $convertedPath = '/media/' . $tp->getId() . '/video/' . $md5 . ".webm";

        $eqi = new EncodingQueueItem();
        $eqi->setType(EncodingQueueItem::VIDEO);
        $eqi->setFrom($this->webDir . $imgNormPath);
        $eqi->setTo($this->webDir . $convertedPath);
        $eqi->setUrl($convertedPath);

        $this->entityManager->persist($eqi);
        $this->entityManager->flush($eqi);

        return [
            'path' => $imgNormPath,
            'attr' => [
                'encoding_id' => $eqi->getId(),
                'width' => 100,
                'height' => 100,
                'time' => $playtime,
                'top' => 0
            ]
        ];
    }

    public function putSlideAudio(Slide $slide, UploadedFile $file)
    {
        /** @var TrainingProgram $tp */
        $tp = $slide->getProgram();

        $path = $this->fileStorage . '/' . $tp->getId() . '/audio';
        $md5 = md5_file($file->getRealPath());
        $name = $md5 . "_orig." . $file->getClientOriginalExtension();
        $file->move($path, $name);
        $imgNormPath = '/media/' . $tp->getId() . '/audio/' . $name;

        $data = $this->getMediaInfo($this->webDir . $imgNormPath);
        $playtime = 0;
        if ($data) {
            $playtime = (int)$data['format']['duration'];
        }

        $convertedPath = '/media/' . $tp->getId() . '/audio/' . $md5 . ".mp3";

        $eqi = new EncodingQueueItem();
        $eqi->setType(EncodingQueueItem::AUDIO);
        $eqi->setFrom($this->webDir . $imgNormPath);
        $eqi->setTo($this->webDir . $convertedPath);
        $eqi->setUrl($convertedPath);

        $this->entityManager->persist($eqi);
        $this->entityManager->flush($eqi);

        return [
            'path' => $imgNormPath,
            'attr' => [
                'encoding_id' => $eqi->getId(),
                'time' => $playtime,
                'audioName' => $file->getClientOriginalName(),
            ]
        ];
    }

    public function initTrainingProgram(TrainingProgram $trainingProgram)
    {
        $id = $trainingProgram->getId();

        $path = $this->fileStorage . '/' . $id;
        $this->filesystem->mkdir($path, 0777);
        $this->filesystem->mkdir($path . '/audio', 0777);
        $this->filesystem->mkdir($path . '/video', 0777);
        $this->filesystem->mkdir($path . '/img', 0777);
    }

    public function removeTrainingProgram(TrainingProgram $trainingProgram)
    {

        $id = $trainingProgram->getId();

        $path = $this->fileStorage . '/' . $id;
        try {
            $this->filesystem->remove($path);
        } catch (\Exception $e) {
            return false;
        }
        return true;

    }

    public function convertVideo($from, $to)
    {
        set_time_limit(0);
        $mp4Path = str_replace('.webm', '.mp4', $to);
        $ffmpegMp4 = new Process("ffmpeg -i '$from' -threads 1 -map_metadata -1 -b:v 2M -strict -2 -b:a 128k -y '$mp4Path'");
        $ffmpegWebm = new Process("ffmpeg -i '$from' -f webm -threads 1 -vcodec libvpx -b:v 2M -acodec libvorbis -b:a 128k -y '$to'");
        $ffmpegMp4->setTimeout(null);
        $ffmpegWebm->setTimeout(null);
        $ffmpegMp4->start();
        $ffmpegWebm->start();
        while ($ffmpegMp4->isRunning() || $ffmpegWebm->isRunning()) {
            // wait
        }
        return $ffmpegMp4->isSuccessful() && $ffmpegWebm->isSuccessful();
    }

    public function convertAudio($from, $to)
    {
        if (pathinfo($from, PATHINFO_EXTENSION) === 'mp3') {
            return copy($from, $to);
        }
        set_time_limit(0);
        $ffmpeg = new Process("ffmpeg -i '$from' -threads 1 -map_metadata -1 -strict -2 -y -b:a 128k '$to'");
        $ffmpeg->start();
        while ($ffmpeg->isRunning()) {
            // wait
        }
        return $ffmpeg->isSuccessful();
    }

    public function getMediaInfo($file)
    {
        set_time_limit(0);
        $ffprobe = new Process("ffprobe -v quiet -print_format json -show_format -show_streams '$file'");
        $ffprobe->start();
        while ($ffprobe->isRunning()) {
            // wait
        }
        if ($ffprobe->isSuccessful()) {
            return json_decode($ffprobe->getOutput(), true);
        }
        return false;
    }

    public function startEncoding()
    {
        $process = new Process('php ../app/console fstraining:encode_slides --env=prod');
        $process->start();
    }
}