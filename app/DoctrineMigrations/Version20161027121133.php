<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161027121133 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $doctrine = $this->container->get('doctrine');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $trainingProgramsRepository = $doctrine->getRepository('FSTrainingProgramsBundle:TrainingProgram');
        $trainingPrograms = $trainingProgramsRepository->findAll();

        foreach ($trainingPrograms as $trainingProgram) {
            if (null === $trainingProgram->getLink()) {
                $trainingProgram->setLink(uniqid('', true));
            }
        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $doctrine = $this->container->get('doctrine');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $trainingProgramsRepository = $doctrine->getRepository('FSTrainingProgramsBundle:TrainingProgram');
        $trainingPrograms = $trainingProgramsRepository->findAll();

        foreach ($trainingPrograms as $trainingProgram) {
            $trainingProgram->setLink(null);
        }

        $em->flush();

    }
}
