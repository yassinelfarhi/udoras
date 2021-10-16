<?php

namespace FS\UserBundle\Model;

use PUGX\MultiUserBundle\Controller\RegistrationManager as RM;
use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Controller\RegistrationController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use PUGX\MultiUserBundle\Form\FormFactory;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;


class RegistrationManager extends RM
{
    public function __construct(UserDiscriminator $userDiscriminator,
                                ContainerInterface $container,
                                RegistrationController $controller,
                                FormFactory $formFactory
    ) {

        parent::__construct(
            $userDiscriminator,
            $container,
            $controller,
            $formFactory
        );
    }

    /**
     *
     * @param string $class
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register($class)
    {
        $this->userDiscriminator->setClass($class);
        $this->controller->setContainer($this->container);
        $result = $this->controller->registerAction($this->getRequest());
        if ($result instanceof RedirectResponse) {
            return $result;
        }

        $template = $this->userDiscriminator->getTemplate('registration');
        if (is_null($template)) {
            $template = 'FOSUserBundle:Registration:register.html.twig';
        }

        $form = $this->formFactory->createForm();
        return $this->container->get('templating')->renderResponse($template, array(
            'form' => $form->createView(),
        ));
    }

    public function registerWithAjax($class)
    {
        $this->userDiscriminator->setClass($class);
        $this->controller->setContainer($this->container);

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $request = $this->getRequest();
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->controller->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return new JsonResponse(
                    [
                        'show-modal-static',
                        $this->controller->renderView('FOSUserBundle:Registration:check_email_modal.html.twig')
                    ]
                );
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return new JsonResponse(
            [
                'error',
                $this->controller->renderView('FOSUserBundle:RegistrationEmployee:register_modal.html.twig', [
                    'form' => $form->createView(),
                    'type' => $class,
                ])
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request;
     */
    private function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}