<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.09.2016
 * Time: 16:45
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Controller;


use FS\UdorasBundle\Form\Type\SetPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CommonController extends Controller
{
    /**
     * @Route("/login_set_password/{token}", name="login_set_password")
     * @param Request $request
     * @Template()
     * @return array
     */
    public function setPasswordAction(Request $request, $token)
    {

        $userManager = $this->get('pugx_user_manager');

        $user = $userManager->findUserBy(['passwordSetToken' => $token]);

        $form = $this->createForm(new SetPasswordType(), $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user->setEnabled(true);
                $user->setPasswordSetToken(null);
                $userManager->updatePassword($user);
                $userManager->updateUser($user, true);

                if ($user->hasRole('ROLE_ADMIN')) {
                    return $this->redirectToRoute("index_admin");
                } elseif ($user->hasRole('ROLE_CUSTOMER')) {
                    return $this->redirectToRoute("index_customer");
                } elseif ($user->hasRole('ROLE_VENDOR')) {
                    return $this->redirectToRoute("index_vendor");
                } elseif ($user->hasRole('ROLE_EMPLOYEE')) {
                    return $this->redirectToRoute("index_employee");
                }

                return $this->redirectToRoute("fs_basic_homepage");
            }
        }

        return [
            'form' => $form->createView(),
            'token' => $token,
        ];
    }

    /**
     * @Route("/resend_confirmation_email", name="email_confirmation_resend")
     * @param Request $request
     * @return JsonResponse
     */
    public function resendConfirmationEmailAction(Request $request)
    {
        $userManager = $this->get('pugx_user_manager');

        $user = $userManager->findUserBy(['email' => $request->request->get('user', 'nomail'), 'enabled' => false]);
        if ($user) {
            if($user->getConfirmationToken() !== null){
                $this->get('fos_user.mailer')->sendConfirmationEmailMessage($user);
            } else {
                $this->get('fs.mailer')->sendLoginEmailMessage($user);
            }

        }
        return new JsonResponse(['processed']);
    }


    /**
     * @Route("/get_logout_form", name="get_logout_form")
     * @param Request $request
     *
     * @Template()
     */
    public function logoutConfirmAction(Request $request)
    {

    }
}