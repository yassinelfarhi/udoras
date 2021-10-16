<?php

namespace FS\TrainingProgramsBundle\Controller;


use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\TrainingProgramsBundle\Form\Type\TrainingProgramFilterType;
use FS\UdorasBundle\Annotation\Resource;
use FS\UdorasBundle\Annotation\ResourceManipulation;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class TrainingProgramsController extends Controller
{
    /**
     * @param Request $request
     * @param Customer $customer
     * @Route(path="/customer/{customer}/training-programs", name="customer_training_programs")
     * @Security(
     *     expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasAccessTo(customer)"
     * )
     * @Template()
     */
    public function indexAction(Request $request, Customer $customer)
    {
        $form = $this->get('form.factory')->create(new TrainingProgramFilterType());
        $formName = $form->getName();
        $trainingProgramsQuery = $this->getDoctrine()
            ->getRepository('FSTrainingProgramsBundle:TrainingProgram')
            ->getFindAllByCustomerRaw($customer);

        if ($request->query->has($formName)) {
            $form->submit($request->query->get($formName));
            $this->get('lexik_form_filter.query_builder_updater')
                ->addFilterConditions($form, $trainingProgramsQuery);
        }

        $paginator = $this->get('knp_paginator')
            ->paginate($trainingProgramsQuery, $request->query->get('page', 1), 30);

        if ($this->getUser()->hasRole('ROLE_ADMIN')) {
            $view = 'FSTrainingProgramsBundle:TrainingPrograms:adminIndex.html.twig';
        } else {
            $view = 'FSTrainingProgramsBundle:TrainingPrograms:index.html.twig';
        }

        $this->get('session')->set('training-return', $this->generateUrl('customer_training_programs', ['customer' => $customer->getId()]));

        return $this->render($view, [
            'form' => $form->createView(),
            'customer' => $customer,
            'paginator' => $paginator
        ]);
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \InvalidArgumentException
     * @Route(path="/training-programs/{link}", name="show_training_program", options = { "expose" = true })
     * @ParamConverter("trainingProgram", class="FSTrainingProgramsBundle:TrainingProgram")
     *
     * @Template()
     */
    public function showAction(Request $request, TrainingProgram $trainingProgram)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            $session = $this->get('session');
            $session->set('next-page', $this->generateUrl('show_training_program', ['link' => $trainingProgram->getLink()]));
            return new RedirectResponse('/#login');
        } else {
            if ($user->hasRole('ROLE_CUSTOMER') && $user !== $trainingProgram->getCustomer()) {
                return $this->render('@FSTrainingPrograms/TrainingPrograms/trainingProgramAccessError.html.twig');
            }
        }

        if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_CUSTOMER')) {
            $linksQuery = $this->getDoctrine()
                ->getRepository('FSTrainingProgramsBundle:Link')
                ->getFindAllByTrainingProgramQuery($trainingProgram);
            $linksPagination = $this->get('knp_paginator')
                ->paginate($linksQuery, $request->query->get('page', 1), 30);

            return [
                'trainingProgram' => $trainingProgram,
                'linksPagination' => $linksPagination
            ];
        } elseif ($user->hasRole('ROLE_VENDOR')) {
            $request = $this->getDoctrine()
                ->getRepository('FSTrainingProgramsBundle:Request')
                ->findRequestByVendorAndTrainingProgram($user, $trainingProgram);

            if (is_null($request)) {
                $requestManager = $this->get('fs_training.model.manager.request_manager');
                $request = $requestManager->createRequest($user, $trainingProgram);
            }

            return $this->render('FSTrainingProgramsBundle:TrainingPrograms:vendor/show.html.twig', [
                'trainingProgram' => $trainingProgram,
                'request' => $request
            ]);
        } else {
            $access = $this->getDoctrine()
                ->getRepository('FSTrainingProgramsBundle:Access')
                ->findByEmployeeAndTrainingProgram($user, $trainingProgram);

            if (is_null($access)) {
                $accessManager = $this->get('fs_training.model.manager.access_manager');
                $access = $accessManager->createAccess($user, $trainingProgram);
            }

            return $this->render('FSTrainingProgramsBundle:TrainingPrograms:employee/show.html.twig', [
                'trainingProgram' => $trainingProgram,
                'access' => $access
            ]);
        }
    }


    /**
     * @param Request $request
     * @param Customer $customer
     * @throws \InvalidArgumentException
     * @Route(path="admin/customer/{customer}/training-program/create", name="admin_customer_training_program_create")
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_ADMIN')")
     * @Template()
     */
    public function createTrainingProgramAction(Request $request, Customer $customer)
    {
        $trainingProgram = new TrainingProgram();
        $form = $this->get('form.factory')->create(
            $this->get('fs_training.form.type.training_program_type'), $trainingProgram
        );

        $trainingProgram->setCustomer($customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $customer->addTrainingProgram($trainingProgram);

            $em->persist($trainingProgram);
            $em->flush();

            $this->get('fs_training.file_manipulator')->initTrainingProgram($trainingProgram);

            return new RedirectResponse(
                $this->generateUrl('training_program_create_presentation', [
                    'trainingProgram' => $trainingProgram->getId()
                ])
            );
        }

        return [
            'form' => $form->createView(),
            'customer' => $customer,
        ];
    }

    /**
     * @param Request $request
     * @throws \InvalidArgumentException
     * @Route(path="/admin/training-programs/{trainingProgram}/edit", name="admin_training_program_edit")
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_ADMIN')")
     * @Resource(resource="trainingProgram")
     * @Template()
     *
     */
    public function editAction(Request $request, TrainingProgram $trainingProgram)
    {
        $this->getDoctrine()->getEntityManager()->getFilters()->disable('softdeleteable');

        $form = $this->createForm($this->get('fs_training.form.type.training_program_type'), $trainingProgram);
        $form->handleRequest($request);
        $accessManager = $this->get('fs_training.model.manager.access_manager');

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($trainingProgram->getPrice() == 0) {
                /** @var Access $access */
                foreach ($trainingProgram->getAccesses() as $access) {
                    if (empty($access->getTrainingState())) {
                        $accessManager->addNewTrainingStateToAccessAndUpdateState($access, Access::PAID);
                    }
                }
            }
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('show_training_program', [
                    'link' => $trainingProgram->getLink()
                ])
            );
        }

        $linksQuery = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:Link')->getFindAllByTrainingProgramQuery($trainingProgram);
        $linksPagination = $this->get('knp_paginator')->paginate($linksQuery, $request->query->get('page', 1), 30);

        return [
            'form' => $form->createView(),
            'trainingProgram' => $trainingProgram,
            'linksPagination' => $linksPagination
        ];
    }


    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @return array
     * @throws \LogicException
     * @Route(path="admin/customer/training-program/{trainingProgram}/presentation", name="training_program_create_presentation")
     * @Template()
     * @Resource(resource="trainingProgram")
     */
    public function addPresentationAction(Request $request, TrainingProgram $trainingProgram)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Slide $slide */
        foreach ($trainingProgram->getSlides() as $slide) {
            if (
                $slide->getMarker() === Slide::SLIDE_MARKER__DELETED ||
                $slide->getState() === Slide::SLIDE_STATE__NOT_SAVED
            ) {
                $trainingProgram->removeSlide($slide);
                $em->remove($slide);
            }
            if ($slide->getMarker() === Slide::SLIDE_MARKER__MARKED_FOR_DELETE) {
                $slide->setMarker(Slide::SLIDE_MARKER__NO);
                $em->persist($slide);
            }
        }

        $em->flush();

        return [
            'program' => $trainingProgram
        ];
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @return array
     * @Route(path="admin/customer/training-program/{trainingProgram}/end", name="training_program_end_create_presentation")
     * @Template()
     * @ResourceManipulation
     */
    public function finishOrExitAction(Request $request, TrainingProgram $trainingProgram)
    {
        return [
            'program' => $trainingProgram
        ];
    }


    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @throws \LogicException
     * @Route(path="admin/customer/training-program/{trainingProgram}/redirect", name="training_program_redirect")
     * @ResourceManipulation
     */
    public function redirectAfterExitAction(Request $request, TrainingProgram $trainingProgram)
    {
        $customer = $trainingProgram->getCustomer();
        $em = $this->getDoctrine()->getManager();

        /** @var Slide $slide */
        foreach ($trainingProgram->getSlides() as $slide) {
            if (
                $slide->getMarker() === Slide::SLIDE_MARKER__DELETED ||
                $slide->getState() === Slide::SLIDE_STATE__NOT_SAVED
            ) {
                $trainingProgram->removeSlide($slide);
                $em->remove($slide);
            }

            if ($slide->getMarker() === Slide::SLIDE_MARKER__MARKED_FOR_DELETE) {
                $slide->setMarker(Slide::SLIDE_MARKER__NO);
                $em->persist($slide);
            }
        }

        $em->flush();

        if ($trainingProgram->getSlides()->count() > 0) {
            return new RedirectResponse(
                $this->generateUrl('show_training_program', ['link' => $trainingProgram->getLink()])
            );
        }

        $this->get('fs_training.file_manipulator')->removeTrainingProgram($trainingProgram);

        $em->remove($trainingProgram);
        $em->flush();

        return new RedirectResponse(
            $this->generateUrl('customer_training_programs', ['customer' => $customer->getId()])
        );
    }


    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @Route(path="/employee/training/program-result/{link}", name="training_program_result_show")
     * @ParamConverter("trainingProgram", class="FSTrainingProgramsBundle:TrainingProgram", options={"link" = "link"})
     *
     * @Security(
     *     expression="user.hasRole('ROLE_EMPLOYEE')"
     * )
     *
     * @Template()
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \LogicException
     */
    public function trainingResultsAction(Request $request, TrainingProgram $trainingProgram)
    {
        /** @var Employee $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $employeeTS = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getTrainingStateByEmployeeAndProgramFinished($user, $trainingProgram);

        if ($employeeTS === null) {
            return new RedirectResponse(
                $this->generateUrl('index_employee')
            );
        }

        return [
            'trainingProgram' => $trainingProgram,
            'trainingState' => $employeeTS,
        ];
    }
}