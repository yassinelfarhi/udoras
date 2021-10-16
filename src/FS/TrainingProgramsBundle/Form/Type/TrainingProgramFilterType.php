<?php

namespace FS\TrainingProgramsBundle\Form\Type;


use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;


class TrainingProgramFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
            'attr' => [
                'placeholder' => 'search criteria'
            ],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => [
                'filtering',
            ]
        ]);
    }

    /**
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "fs_training_programs_training_program_filter";
    }
}