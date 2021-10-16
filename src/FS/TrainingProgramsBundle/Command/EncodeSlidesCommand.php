<?php

namespace FS\TrainingProgramsBundle\Command;

use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\EncodingQueueItem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EncodeSlidesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fstraining:encode_slides')
            ->setDescription('Encode slides');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = '/tmp/encoding.slides.lock';
        $lockCreated = false;
        try {
            if (file_exists($lock)) {
                $output->writeln("Lock file $lock exists, close another instances");
                return;
            }
            touch($lock);
            $lockCreated = true;
            chmod($lock, 0666);
            /** @var EntityManager $em */
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $repo = $em->getRepository('FSTrainingProgramsBundle:EncodingQueueItem');
            $fm = $this->getContainer()->get('fs_training.file_manipulator');
            /** @var EncodingQueueItem $item */
            while (!empty($item = $repo->getNotEncodedItem())) {
                $output->writeln('Encoding ' . $item->getFrom());
                $this->encodeItem($item);

                $em->persist($item);
                $em->flush($item);
            }
        } finally {
            if ($lockCreated) {
                unlink($lock);
            }
        }
    }

    public function encodeItem(EncodingQueueItem $item)
    {
        $fm = $this->getContainer()->get('fs_training.file_manipulator');
        switch ($item->getType()) {
            case EncodingQueueItem::VIDEO:
                $from = $item->getFrom();
                $to = $item->getTo();

                if ($fm->convertVideo($from, $to)) {
                    $item->setEncodingStatus(EncodingQueueItem::ENCODED);
                } else {
                    $item->setEncodingStatus(EncodingQueueItem::ERROR);
                }
                break;
            case EncodingQueueItem::AUDIO:
                $from = $item->getFrom();
                $to = $item->getTo();

                if ($fm->convertAudio($from, $to)) {
                    $item->setEncodingStatus(EncodingQueueItem::ENCODED);
                } else {
                    $item->setEncodingStatus(EncodingQueueItem::ERROR);
                }
                break;
        }
    }
}
