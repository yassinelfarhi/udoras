<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 10.11.2016
 * Time: 15:05
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Form\Type;


use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

/**
 * Class EmployeeTrainingStateFilterType
 * @package FS\TrainingProgramsBundle\Form\Type
 * @author <vladislav@fora-soft.com>
 */
class EmployeeTrainingStateFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', Filters\TextFilterType::class, [
            'condition_pattern' => FilterOperands::STRING_CONTAINS,
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (is_null($values['value'])) {
                    return null;
                }
                /** @var QueryBuilder $qb */
                $qb = $filterQuery->getQueryBuilder();

                $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                $qb->andWhere(
                    $filterQuery->getExpr()->like('training.title', $value)
                );

            },
            'attr' => [
                'placeholder' => 'search criteria'
            ],
            'mapped' => false
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
        return "fs_training_programs_training_program_state_filter";
    }

}