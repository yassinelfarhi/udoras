<?php

namespace FS\UdorasBundle\Form\Type;


use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TextRangeFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('left_text', TextFilterType::class, $options['left_text_options']);
        $builder->add('right_text', TextFilterType::class, $options['right_text_options']);

        $builder->setAttribute('filter_value_keys', array(
            'left_text'  => $options['left_text_options'],
            'right_text' => $options['right_text_options'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'required'               => false,
                'left_text_options'    => array(),
                'right_text_options'   => array(),
                'data_extraction_method' => 'default',
            ))
            ->setAllowedValues('data_extraction_method', array('default', 'value_keys'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_number_range';
    }
}
