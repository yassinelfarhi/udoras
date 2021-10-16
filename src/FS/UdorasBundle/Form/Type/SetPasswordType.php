<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.09.2016
 * Time: 17:49
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Form\Type;


use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetPasswordType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\RepeatedType'), array(
            'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\PasswordType'),
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Confirm password'),
            'invalid_message' => 'Password Mismatch',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FS\UserBundle\Entity\User',
            'validation_groups' => [
                'Registration',
                'Default'
            ]
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'fs_udoras_set_new_password';
    }
}