<?php

namespace FS\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;


class RegistrationCustomerType extends BaseType
{
    public function __construct()
    {
        parent::__construct('FS\UserBundle\Entity\Customer');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username');
        $builder->remove('email');
        $builder->remove('plainPassword');

        $builder
            ->add('company', 'text',
                [
                    'label' => 'Name of Company',
                    'required' => true,
                ]
            )
            ->add('fullName', 'text',
                [
                    'label' => 'Contact Name',
                    'required' => true,
                ]
            )
            ->add('email', 'email',
                [
                    'label' => 'Contact Email',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Email()
                    ]
                ]
            )
            ->add('phone', 'text',
                [
                    'label' => 'Phone Number',
                    'required' => true
                ]
            )
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'required' => true,
                'first_options' => [
                    'label' => 'Password'
                ],
                'second_options' => [
                    'label' => 'Repeat Password'
                ],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new NotBlank(),
                ],
                'options' => [
                    'attr' => [
                        'class' => 'password-field'
                    ]
                ],
            ])
            ->add('street', 'text',
                [
                    'label' => 'Street',
                    'required' => true,
                ]
            )
            ->add('city', 'text',
                [
                    'label' => 'City',
                    'required' => true,

                ]
            )
            ->add('state', 'text',
                [
                    'label' => 'State',
                    'required' => false,
                    'render_optional_text' => false
                ]
            )
            ->add('country', 'genemu_jqueryselect2_country',
                [
                    'label' => 'Country',
                    'required' => true,
                    'attr' => ['placeholder' => 'Country'],
                    'empty_value' => '',
                ]
            )
            ->add('zipCode', 'text', [
                    'required' => false,
                    'render_optional_text' => false
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FS\UserBundle\Entity\Customer',
            'validation_groups' => [
                'CustomerRegistration',
                'CustomRegistration',
                'Default',
            ],

        ));
    }

    public function getName()
    {
        return 'fs_user_registration_customer_form';
    }
}