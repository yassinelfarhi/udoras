<?php

namespace FS\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationEmployeeType extends BaseType
{
    public function __construct()
    {
        parent::__construct('FS\UserBundle\Entity\Employee');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username');
        $builder->remove('email');
        $builder->remove('plainPassword');

        $builder->add('fullName', 'text', [
                'label' => 'Name',
                'required' => true,
            ]
        )
            ->add('email', 'email',
                [
                    'label' => 'Email',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Email()
                    ]
                ]
            )
            ->add('phone', 'text', [
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
            ->add('lastFourOfSSN', 'text', [
                'label' => 'Last four Of SSN',
                'required' => true,
            ])
            ->add('birthday', 'collot_datetime', [
                'widget' => 'single_text',
                'label' => 'Birthday',
                'required' => true,
                'pickerOptions' => [
                    'format' => 'dd M yyyy',
                    'minView' => 2,
                    'keyboardNavigation' => true,
                    'autoclose' => true,
                    'startDate' => date('d M Y', strtotime(date('d M Y') . '-80 year')),
                    'initialDate' => date('D M Y',  strtotime(date('d M Y') . '-30 year')),
                ]
            ])
            ->add('phone', 'text', [
                    'label' => 'Phone Number',
                    'required' => true]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'FS\UserBundle\Entity\Employee',
            'validation_groups' => [
                'CustomRegistration',
                'EmployeeRegistration',
                'Default',
            ]
        ]);
    }

    /* public function getParent()
     {
         return 'fos_user_registration';
     }*/

    public function getName()
    {
        return 'fs_user_registration_employee_form';
    }
}