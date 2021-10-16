<?php

namespace FS\UserBundle\Form\Type;

use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationVendorType extends BaseType
{
    public function __construct()
    {
        parent::__construct("FS\\UserBundle\\Entity\\Vendor");
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username');
        $builder->remove('email');
        $builder->remove('plainPassword');

        $builder->add('fullName', 'text',
            [
                'label' => 'Vendor Name',
                'required' => true,
            ]
        )
            ->add('email', 'email',
                [
                    'label' => 'Vendor Contact Email',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Email()
                    ]
                ]
            )
            ->add('plainPassword', 'repeated',
                [
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
                    'required' => true
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
            ->add('zipCode', 'text',
                [
                    'label' => 'Zip Code',
                    'required' => false,
                    'render_optional_text' => false
                ]
            )
            ->add('contactPersonName', 'text',
                [
                    'label' => 'Name',
                    'required' => true
                ]
            )
            ->add('phone', 'text',
                [
                    'label' => 'Phone Number',
                    'required' => true,
                    'validation_groups' => [
                        'Default'
                    ],
                ]
            );
        $builder->remove('username');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FS\UserBundle\Entity\Vendor',
            'cascade_validation' => true,
            'validation_groups' => [
                'CustomRegistration',
                'VendorRegistration',
                "Default"
            ]
        ));
    }

    public function getName()
    {
        return 'fs_user_registration_vendor_form';
    }
}