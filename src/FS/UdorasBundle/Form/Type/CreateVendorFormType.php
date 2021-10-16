<?php

namespace FS\UdorasBundle\Form\Type;


use FOS\UserBundle\Model\UserManager;
use FS\UserBundle\Entity\User;
use FS\UserBundle\Entity\Vendor;
use FS\UserBundle\Form\Type\RegistrationVendorType;
use Sortable\Fixture\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Form\FormEvent;

class CreateVendorFormType extends RegistrationVendorType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage = null)
    {
        parent::__construct();
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('plainPassword');

        if ($user = $this->getUser()) {
            if ($user->hasRole('ROLE_ADMIN')) {
                $builder->add('customer', 'genemu_jqueryselect2_entity', [
                    'label' => 'Customer contact email',
                    'class' => 'FS\UserBundle\Entity\Customer',
                    'property' => 'email',
                    'required' => false,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'customer-email'
                    ]
                ]);
            } else if ($user->hasRole('ROLE_CUSTOMER')) {
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                    $data = $event->getData();
                    $data->setCustomer($user);
                });
            }

            $builder->add('customer_name', 'text', [
                'mapped' => false,
                'label' => 'Name of Company',
                'attr' => ['class' => 'customer-name', 'readonly' => true]
            ]);

            /** @var Vendor $data */
            $data = $builder->getData();
            if($data->getCustomer() != null){
                $builder->get('customer_name')->setData($data->getCustomer()->getCompany());
            }
            if ($user->hasRole('ROLE_CUSTOMER')) {
                $builder->get('customer_name')->setData($this->getUser()->getCompany());
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FS\UserBundle\Entity\Vendor',

        ));
    }

    /**
     * get User for dynamically generate form
     *
     * @return mixed|null
     */
    private function getUser()
    {
        if (!$this->tokenStorage) {
            return null;
        }
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }
        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "fs_user_create_vendor_form";
    }
}