<?php

namespace FS\BasicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="fs_basic_homepage")
     */
    public function indexAction()
    {
        $auth = $this->get('security.authorization_checker');
        if ($auth->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('index_admin');
        }
        if ($auth->isGranted('ROLE_CUSTOMER')) {
            return $this->redirectToRoute('index_customer');
        }
        if ($auth->isGranted('ROLE_VENDOR')) {
            return $this->redirectToRoute('index_vendor');
        }
        if ($auth->isGranted('ROLE_EMPLOYEE')) {
            return $this->redirectToRoute('index_employee');
        }
        return $this->redirectToRoute('fos_user_security_login');
    }

    /**
     * @Route("/thanks", name="fs_home_thanks")
     * @Template()
     */
    public function afterLogoutAction()
    {
        return $this->redirectToRoute('fos_user_security_login');
    }

    /**
     * @Route("/select-registration", name="fs_basic_select_registration")
     */
    public function selectRegistrationAction()
    {
        return $this->render('FSBasicBundle:Default:select_registration.html.twig');
    }

    /**
     * @Route("/privacy-policy", name="fs_basic_privacy_policy_page")
     */
    public function privacyPolicyAction(Request $request)
    {
        $prevPageUrl = $request->headers->get('referer');

        return $this->render('FSBasicBundle:Default:privacy_policy.html.twig', [
            'prevPageUrl' => $prevPageUrl
        ]);
    }

    /**
     * @Route("/terms", name="fs_basic_terms_page")
     */
    public function termsAction(Request $request)
    {
        $prevPageUrl = $request->headers->get('referer');

        return $this->render('FSBasicBundle:Default:terms.html.twig', [
            'prevPageUrl' => $prevPageUrl
        ]);
    }
}
