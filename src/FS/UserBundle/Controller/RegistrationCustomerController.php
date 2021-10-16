<?php

namespace FS\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RegistrationCustomerController extends Controller
{
    /**
     * @Route("/register/customer", name="fs_user_register_customer")
     *
     * @Security(expression="is_granted('ROLE_ADMIN')")
     */
    public function registerAction()
    {
        return $this->container
                    ->get('pugx_multi_user.registration_manager')
                    ->register('FS\UserBundle\Entity\Customer');
    }
}