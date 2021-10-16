<?php

namespace FS\UdorasBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserManager;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\Vendor;
use FS\UserBundle\Form\Type\RegistrationEmployeeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CreateEmployeeType extends RegistrationEmployeeType
{
    private $tokenStorage;

    public function __construct(TokenStorage $tokenStorage = null, UserManager $userManager = null)
    {
        parent::__construct();
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('plainPassword');

        $data = $builder->getData();

        if ($user = $this->getUser()) {
            if ($user->hasRole('ROLE_ADMIN')) {
                $builder->add('vendor', 'genemu_jqueryselect2_entity', [
                    'label' => 'Vendor Email',
                    'class' => 'FS\UserBundle\Entity\Vendor',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository
                            ->createQueryBuilder('vendor')
                            ->where('vendor.roles LIKE :roles')
                            ->setParameter('roles', '%ROLE_VENDOR%')
                            ->orderBy('vendor.fullName', 'ASC');
                    },
                    'property' => 'email',
                    'placeholder' => "empty",
                    "required" => false,
                    'attr' => ['class' => 'vendor-email'],
                ])->add('vendor_name', 'text', [
                    'mapped' => false,
                    'label' => 'Vendor Name',
                    'attr' => ['class' => 'vendor-name', 'readonly' => true]
                ]);

                /** @var Employee $data */
                if($data->getVendor() != null){
                    $builder->get('vendor_name')->setData($data->getVendor()->getFullName());
                };
            } elseif ($user->hasRole('ROLE_CUSTOMER')) {

            } elseif ($user->hasRole('ROLE_VENDOR')){
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                    $data = $event->getData();
                    $data->setVendor($user);
                });
            } elseif ($user->hasRole('ROLE_EMPLOYEE')) {
                if($data->getVendor() != null){
                    $builder->add('vendor_email', 'text', [
                        'mapped' => false,
                        'disabled' => true,
                        'label' => 'Vendor Email',
                        'data' => $data->getVendor()->getEmail(),
                        'attr' => ['class' => 'vendor-email']
                    ])->add('vendor_name', 'text', [
                        'mapped' => false,
                        'disabled' => true,
                        'label' => 'Vendor Name',
                        'data' => $data->getVendor()->getFullName(),
                        'attr' => ['class' => 'vendor-name']
                    ]);
                };
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FS\UserBundle\Entity\Employee',
            'validation_groups' => [
                'EmployeeCreate',
                'Creating',
                'Default'
            ]
        ));
    }

    public function getName()
    {
        return 'fs_user_create_employee_form';
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

}