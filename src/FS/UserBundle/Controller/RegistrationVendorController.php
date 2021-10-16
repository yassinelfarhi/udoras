<?php

namespace FS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RegistrationVendorController extends Controller
{
    /**
     * @Route("/register/vendor", name="fs_user_register_vendor")
     */
    public function registerAction(Request $request)
    {
        $registrationManager = $this->container->get('fs_user.model.registration_manager');

        if ($request->isXmlHttpRequest()) {
            return $registrationManager->registerWithAjax('FS\UserBundle\Entity\Vendor');
        }

        return $registrationManager->register('FS\UserBundle\Entity\Vendor');
    }
}