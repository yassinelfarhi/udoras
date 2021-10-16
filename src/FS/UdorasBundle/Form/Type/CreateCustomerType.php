<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.09.2016
 * Time: 16:08
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Form\Type;

use FS\UserBundle\Form\Type\RegistrationCustomerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCustomerType extends RegistrationCustomerType
{
    public function __construct()
    {
        parent::__construct();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('plainPassword');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FS\UserBundle\Entity\Customer',
            'validation_groups' => [
                'CustomerCreate',
                'Creating',
                'Default'
            ]
        ));
    }


    public function getName()
    {
        return 'fs_user_create_customer_form';
    }

}