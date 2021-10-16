<?php

namespace FS\TrainingProgramsBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('trainings', 'text', [
            'label' => 'Trainings available:',
            'data' => 1
        ])->add('comment', 'text', [
            'label' => 'Comment:'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'FS\TrainingProgramsBundle\Entity\Link',
        ]);
    }

    public function getName()
    {
        return 'fs_training_program_link';
    }

}