<?php

namespace FS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RegistrationEmployeeController extends Controller
{
    /**
     * @Route("/register/employee", name="fs_user_register_employee")
     */
    public function registerAction(Request $request)
    {
        $registrationManager = $this->container->get('fs_user.model.registration_manager');

        if ($request->isXmlHttpRequest()) {
            return $registrationManager->registerWithAjax('FS\UserBundle\Entity\Employee');
        }

        return $registrationManager->register('FS\UserBundle\Entity\Employee');
    }
}