<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 27.09.2016
 * Time: 15:38
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Form\Type;


use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

/**
 * Class UserFilterType
 * @package FS\UdorasBundle\Form\Type
 * @author <vladislav@fora-soft.com>
 */
class UserFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'query',
            Filters\TextFilterType::class,
            [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => ['placeholder' => 'Enter name, phone number or email'],
            ]
        );
    }

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
        return "fs_udoras_user_filter";
    }
}