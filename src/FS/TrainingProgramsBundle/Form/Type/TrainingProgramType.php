<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 06.10.2016
 * Time: 15:39
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Form\Type;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TrainingProgramType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', [
            'required' => true,
            'label' => 'Title:'
        ]);

        $trainingProgram = $builder->getData();

        if (is_null($trainingProgram->getId()) || $this->hasQuestions($trainingProgram)) {
            $builder->add('passing', 'text', [
                'required' => true,
                'label' => 'Passing:'
            ]);
        } else {
            $builder->add('passing', 'text', [
                'label' => 'Passing:',
                'data' => 'N\A',
                'disabled' => true,
                'attr' => ['title' => 'Add questions to be able to set Passing %']
            ]);
        }

        $builder->add('price', 'text', [
            'required' => true,
            'label' => 'Price:'
        ]);

        $builder->add('certificateValidMonths', 'text', [
            'required' => true,
            'label' => 'Certificate valid for:'
        ]);

        $builder->add('certificateValidUntil', 'collot_datetime', [
            'widget' => 'single_text',
            'label' => 'Certificate valid until:',
            'required' => true,
            'pickerOptions' => [
                'format' => 'dd M yyyy',
                'minView' => 2,
                'keyboardNavigation' => true,
                'autoclose' => true,
                'startDate' => date('d M Y')
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'FS\TrainingProgramsBundle\Entity\TrainingProgram',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "fs_training_training_program_type";
    }

    /**
     * Check if Training Program has slides with questions
     *
     * @param TrainingProgram $trainingProgram
     * @return mixed
     */
    private function hasQuestions(TrainingProgram $trainingProgram)
    {
        return $this->entityManager->getRepository('FSTrainingProgramsBundle:Slide')
            ->createQueryBuilder('slide')
            ->select('count(slide)')
            ->where('slide.program = :trainingProgram AND slide.slideType = :slideType')
            ->setParameters([
                'trainingProgram' => $trainingProgram,
                'slideType' => Slide::SLIDE_TYPE__QUESTION
            ])->getQuery()->getSingleScalarResult();
    }
}