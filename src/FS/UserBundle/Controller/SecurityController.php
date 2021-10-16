<?php

namespace FS\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use FOS\UserBundle\Model\User;
use FS\TrainingProgramsBundle\Entity\Link;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


/**
 * Class SecurityController
 * @package FS\UserBundle\Controller
 * @author <vladislav@fora-soft.com>
 */
class SecurityController extends BaseController
{
    public function loginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        if (class_exists('\Symfony\Component\Security\Core\Security')) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
        } else {
            // BC for SF < 2.6
            $authErrorKey = SecurityContextInterface::AUTHENTICATION_ERROR;
            $lastUsernameKey = SecurityContextInterface::LAST_USERNAME;
        }

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        if ($this->has('security.csrf.token_manager')) {
            $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        } else {
            // BC for SF < 2.4
            $csrfToken = $this->has('form.csrf_provider')
                ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null;
        }

        $data = [
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ];

        $session = $request->getSession();
        $session->set('redirect_after_login', $request->headers->get('referer'));

        if ($request->isXmlHttpRequest() && $error !== null) {
            return new JsonResponse(
                [
                    'error',
                    $this->renderView('FOSUserBundle:Security:login.html.twig', $data)
                ]
            );
        }
        return $this->renderLogin($data);
    }

    public function loginSuccessAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $target = $this->generateUrl('fs_basic_homepage');
        if ($user->hasRole('ROLE_ADMIN')){
            $target = $this->generateUrl('index_admin');
        } elseif ($user->hasRole('ROLE_CUSTOMER')){
            $target = $this->generateUrl('index_customer');
        } elseif ($user->hasRole('ROLE_VENDOR')){
            $target = $this->generateUrl('index_vendor');
        }elseif ($user->hasRole('ROLE_EMPLOYEE')){
            $session = $this->get('session');
            $nextLink = $session->get('next-page',null);
            /** @var $freeLink Link */
            if($nextLink){
                $target = $nextLink;
                $session->remove('next-page');
            } else {
                $target = $this->generateUrl('index_employee');
            }

        }

        $redirectUrl = $request->getSession()->get('next-page');

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['processed', $redirectUrl ? $redirectUrl : $target]);
        }

        return new RedirectResponse(
            $redirectUrl ? $redirectUrl : $target
        );
    }
}
