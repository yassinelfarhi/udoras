<?php

namespace FS\UdorasBundle\Controller\Admin;


use FS\TrainingProgramsBundle\Form\Type\EmployeeTrainingStateFilterType;
use FS\TrainingProgramsBundle\Util\CheckFilterForNull;
use FS\UdorasBundle\Form\Type\CreateCustomerType;
use FS\UdorasBundle\Form\Type\PaymentsReportsFilterType;
use FS\UdorasBundle\Form\Type\TrainingReportFilterType;
use FS\UdorasBundle\Form\Type\UserFilterType;
use FS\UserBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class AdminController
 * @package FS\UdorasBundle\Controller
 * @author <vladislav@fora-soft.com>
 *
 * @Security(expression="is_granted('ROLE_ADMIN')")
 */
class AdminController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @Route(path="", name="index_admin")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form         = $this->get('form.factory')->create(new UserFilterType());
	    $repo         = $this->getDoctrine()->getRepository('FSUserBundle:Customer');
	    $customersRaw = $repo->getFindAllRaw();

        $aa = $this->getDoctrine()->getRepository('FSUserBundle:User')->getAllDeleted();
        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $repo->addFilterConditions($form->get('query')->getData(), $customersRaw);
        }

        $paginator = $this->get('knp_paginator')
            ->paginate($customersRaw->getQuery(), $request->query->get('page', 1), 30);

        return [
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @Route(path="/vendors", name="admin_vendors")
     * @Template()
     */
    public function vendorsListAction(Request $request)
    {
        $form       = $this->get('form.factory')->create(new UserFilterType());
	    $repo       = $this->getDoctrine()->getRepository('FSUserBundle:Vendor');
	    $vendorsRaw = $repo->getFindAllRaw();

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $repo->addFilterConditions($form->get('query')->getData(), $vendorsRaw);
        }

        $paginator = $this->get('knp_paginator')
            ->paginate($vendorsRaw->getQuery(), $request->query->get('page', 1), 30);

        return [
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="/employee", name="admin_employee")
     * @Template()
     */
    public function employeeListAction(Request $request)
    {
        $form         = $this->get('form.factory')->create(new UserFilterType());
	    $repo         = $this->getDoctrine()->getRepository('FSUserBundle:Employee');
	    $employeesRaw = $repo->getFindAllRaw();

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $repo->addFilterConditions($form->get('query')->getData(), $employeesRaw);
        }

        $paginator = $this->get('knp_paginator')
            ->paginate($employeesRaw->getQuery(), $request->query->get('page', 1), 30);

        return [
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="/new/customer", name="admin_new_customer")
     * @Template()
     */
    public function newCustomerAction(Request $request)
    {
        $customer = new Customer();
        $form = $this->createForm('fs_user_create_customer_form', $customer);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="/new/customer/create", name="admin_create_customer")
     * @Template("FSUdorasBundle:Admin/Admin:newCustomer.html.twig")
     */
    public function createCustomerAction(Request $request)
    {
        $discriminator = $this->get('pugx_user.manager.user_discriminator');
        $discriminator->setClass('FS\UserBundle\Entity\Customer');
        $userManager = $this->get('pugx_user_manager');

        /** @var Customer $customer */
        $customer = $userManager->createUser();

        $customer->setPassword(uniqid('', true));
        $customer->setPlainPassword(uniqid('', true));
        $customer->setPasswordSetToken(md5(uniqid('', true)));
        $customer->setEnabled(false);

        $form = $this->createForm('fs_user_create_customer_form', $customer);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager->updateUser($customer, true);

            $this->get('fs.mailer')->sendLoginEmailMessage($customer);

            return new JsonResponse(
                [
                    'show-modal-static',
                    $this->renderView('@FSUdoras/userCreated.html.twig')
                ]
            );

            //return $this->redirectToRoute("index_admin");
        }

        return new JsonResponse(['error',
            $this->renderView('@FSUdoras/Admin/Admin/newCustomer_modal.html.twig', [
                'form' => $form->createView(),
            ])
        ]);
    }

    /**
     * @param Request $request
     * @Route(path="/certificates/list", name="admin_view_certificates_list")
     * @Template()
     * @return array
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \RuntimeException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function certificatesListAction(Request $request)
    {

        $form = $this->get('form.factory')
            ->create(new EmployeeTrainingStateFilterType());

        $em = $this->getDoctrine()
            ->getManager();

        $trainingProgramsCompletedRaw = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getAllTrainingStateFullyFinishedWithSoftDeletedQueryBuilder();

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $trainingProgramsCompletedRaw);
        }

        $q = $trainingProgramsCompletedRaw->getQuery()->getResult();
        //$q = array_unique($q);

        $paginator = $this->get('knp_paginator')->paginate($q, $request->query->get('page', 1), 30);

        return [
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws \RuntimeException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @Route(path="/reports/trainings", name="admin_reports_training_action")
     *
     * @Template()
     */
    public function reportTrainingsAction(Request $request)
    {
        $form = $this->get('form.factory')
            ->create(new TrainingReportFilterType());

        $em = $this->getDoctrine()
            ->getManager();

        $trainingsReportsRaw = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getAllTrainingStatesWithSoftDeleted();

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->get('lexik_form_filter.query_builder_updater')
                ->addFilterConditions($form, $trainingsReportsRaw);
        }

        $q = $trainingsReportsRaw->getQuery()->getResult();

        $paginator = $this->get('knp_paginator')
            ->paginate(
                $q,
                $request->query->get('page', 1), 30
            );
        $this->get('session')->set('training-return', $this->generateUrl('admin_reports_training_action'));

        return [
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }


    /**
     * @param Request $request
     * @return Response
     * @throws \RuntimeException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *
     * @Route(path="/reports/trainings/pdf", name="admin_reports_training_pdf_action")
     */
    public function getReportTrainingsPDFAction(Request $request)
    {
        $form = $this->get('form.factory')
            ->create(new TrainingReportFilterType());

        $em = $this->getDoctrine()
            ->getManager();

        $trainingsReportsRaw = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getAllTrainingStatesWithSoftDeleted();

        $hasFilter = false;
        $filterConditions = [];
        if ($request->query->has($form->getName())) {
            $hasFilter = true;
            $form->submit($request->query->get($form->getName()));
            $filterConditions = $request->query->all()[$form->getName()];
            if(!CheckFilterForNull::check($filterConditions)){
                $hasFilter = false;
            }
            $this->get('lexik_form_filter.query_builder_updater')
                ->addFilterConditions($form, $trainingsReportsRaw);
        }

        $trainingsReports = $trainingsReportsRaw
            ->getQuery()
            ->getResult();

        $pdfGenerator = $this->get('knp_snappy.pdf');

        $html = $this->renderView('@FSUdoras/Report/trainingReportPDF.html.twig', [
            'trainingReports' => $trainingsReports,
            'hasFilter' => $hasFilter,
            'filterConditions' => $filterConditions,
        ]);

        return new Response($pdfGenerator->getOutputFromHtml($html, ['encoding' => 'utf-8']),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'attachment; filename="' .
                    'Training Report' .
                    '.pdf"'
            ]
        );
    }

    /**
     * @param Request $request
     * @return array
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @Route(path="/reports/payments", name="admin_reports_payment_action")
     *
     * @Template()
     */
    public function reportPaymentsAction(Request $request)
    {
        $form = $this->get('form.factory')->create(new PaymentsReportsFilterType());
        $em = $this->getDoctrine()->getManager();
        $paymentsRaw = $em->getRepository('FSTrainingProgramsBundle:Payment')->getPaymentsGroupedByTrainingsWithSoftDeletedRaw();

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $paymentsRaw);
        }

        $paginator = $this->get('knp_paginator')->paginate(
            $paymentsRaw->getQuery()->getResult(),
            $request->query->get('page', 1), 30
        );

        return [
            'paginator' => $paginator,
            'form' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \RuntimeException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     *
     * @Route(path="/reports/payments/pdf", name="admin_reports_payment_pdf_action")
     */
    public function getReportPaymentsPDFAction(Request $request)
    {
        $form = $this->get('form.factory')->create(new PaymentsReportsFilterType());
        $em = $this->getDoctrine()->getManager();
        $paymentsRaw = $em->getRepository('FSTrainingProgramsBundle:Payment')->getPaymentsGroupedByTrainingsWithSoftDeletedRaw();

        $hasFilter = false;
        $filterConditions = [];
        if ($request->query->has($form->getName())) {
            $hasFilter = true;
            $form->submit($request->query->get($form->getName()));
            $filterConditions = $request->query->all()[$form->getName()];
            if(!CheckFilterForNull::check($filterConditions)){
                $hasFilter = false;
            }
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $paymentsRaw);
        }

        $payments = $paymentsRaw->getQuery()->getResult();

        $pdfGenerator = $this->get('knp_snappy.pdf');
        
        $html = $this->renderView('@FSUdoras/Report/paymentReportPDF.html.twig', [
            'payments' => $payments,
            'hasFilter' => $hasFilter,
            'filterConditions' => $filterConditions,
        ]);
        
        return new Response($pdfGenerator->getOutputFromHtml($html, ['encoding' => 'utf-8']),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' =>
                    'attachment; filename="' .
                    'Payment Report' .
                    '.pdf"'
            ]
        );
    }
}
