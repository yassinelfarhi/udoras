<?php

namespace FS\UdorasBundle\Controller\Customer;


use FS\TrainingProgramsBundle\Form\Type\EmployeeTrainingStateFilterType;
use FS\TrainingProgramsBundle\Util\CheckFilterForNull;
use FS\UdorasBundle\Annotation\Resource;
use FS\UdorasBundle\Annotation\ResourceManipulation;
use FS\UdorasBundle\Form\Type\CreateCustomerType;
use FS\UdorasBundle\Form\Type\TrainingReportFilterType;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Vendor;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FS\UdorasBundle\Form\Type\CreateVendorFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FS\UdorasBundle\Form\Type\UserFilterType;

/**
 * Class CustomerController
 * @package FS\UdorasBundle\Controller\Customer
 * @author <vladislav@fora-soft.com>
 *
 * @Security(expression="is_granted('ROLE_CUSTOMER') || is_granted('ROLE_ADMIN')")
 */
class CustomerController extends Controller
{
    /**
     * @param Request $request
     * @Route(path="", name="index_customer")
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
        $form       = $this->get('form.factory')->create(new UserFilterType());
	    $repo       = $this->getDoctrine()
	                       ->getRepository('FSUserBundle:Vendor');
	    $vendorsRaw = $repo->getFindAllByCustomerRaw($this->getUser());

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $repo->addFilterConditions($form->get('query')->getData(), $vendorsRaw, false);
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
     * @param Vendor $vendor
     * @return array
     * @throws \LogicException
     * @Template()
     * @Route(path="/employees/{vendor}", name="index_customer_vendor_employees")
     * @Security(expression="user.hasAccessTo(vendor)")
     */
    public function viewVendorEmployeesAction(Request $request, Vendor $vendor)
    {
        $form         = $this->get('form.factory')->create(new UserFilterType());
	    $repo         = $this->getDoctrine()->getRepository('FSUserBundle:Employee');
	    $employeesRaw = $repo->getFindAllByVendorRaw($vendor);

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $repo->addFilterConditions($form->get('query')->getData(), $employeesRaw, false);
        }

        $paginator = $this->get('knp_paginator')
            ->paginate($employeesRaw->getQuery(), $request->query->get('page', 1), 30);

        return [
            'vendor' => $vendor,
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }
    /**
     * @param Request $request
     * @param Vendor $vendor
     * @return array
     * @Route(path="/employees/{vendor}/new", name="new_customer_vendor_employee")
     * @Security(expression="user.hasAccessTo(vendor)")
     * @Template()
     */
    public function newVendorEmployeeAction(Request $request, Vendor $vendor)
    {
        $employee = new Employee();
        $form = $this->createForm('fs_user_create_employee_form', $employee);

        return [
            'vendor' => $vendor,
            'form' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @Route(path="/employees/{vendor}/create", name="create_customer_vendor_employee")
     * @Security(expression="user.hasAccessTo(vendor)")
     * @Template("FSUdorasBundle:Customer/Customer:newVendorEmployee.html.twig")
     */
    public function createVendorEmployeeAction(Request $request, Vendor $vendor)
    {
        $discriminator = $this->get('pugx_user.manager.user_discriminator');
        $discriminator->setClass('FS\UserBundle\Entity\Employee');
        $userManager = $this->get('pugx_user_manager');

        /** @var Employee $employee */
        $employee = $userManager->createUser();
        $form = $this->createForm('fs_user_create_employee_form', $employee);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $employee->setPassword(uniqid('', true));
            $employee->setPlainPassword(uniqid('', true));
            $employee->setPasswordSetToken(md5(uniqid('', true)));
            $employee->setEnabled(false);
            $employee->setVendor($vendor);

            $userManager->updateUser($employee, true);

            $this->get('fs.mailer')->sendLoginEmailMessage($employee);

            return new JsonResponse(
                [
                    'show-modal',
                    $this->renderView('@FSUdoras/userCreated.html.twig', [
                        'link' => $this->generateUrl('index_customer_vendor_employees', ['vendor' => $vendor->getId()]),
                    ]),
                ]
            );
        }

        return new JsonResponse(['error',
            $this->renderView('@FSUdoras/Customer/Customer/newVendorEmployee_modal.html.twig', [
                'form' => $form->createView(),
                'vendor' => $vendor
            ])
        ]);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @Template()
     * @Route(path="/profile/{customer}", name="index_customer_profile")
     * @Security(expression="user.hasAccessTo(customer)")
     * @return array
     */
    public function customerProfileAction(Request $request, Customer $customer)
    {
        return [
            "customer" => $customer,
        ];
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @Template()
     * @Route(path="/profile/{customer}/edit", name="customer_edit_profile")
     * @Security(expression="(user.getId() == customer.getId() || is_granted('ROLE_ADMIN'))")
     * @Resource(resource="customer")
     * @return array|Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function customerEditProfileAction(Request $request, Customer $customer)
    {
        $form = $this->createForm(new CreateCustomerType(), $customer);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($customer);
                $em->flush();

                return new RedirectResponse(
                    $this->generateUrl('index_customer_profile',
                        [
                            'customer' => $customer->getId(),
                        ]
                    )
                );
            }
        }

        return [
            "customer" => $customer,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @Route(path="/new/vendor", name="customer_new_vendor")
     * @Template()
     * @ResourceManipulation()
     * @return array|JsonResponse
     */
    public function newVendorAction(Request $request)
    {
        // for autocomplete Customer name in vendor creation form
        if ($request->isXmlHttpRequest()) {
            $userManager = $this->get('fos_user.user_manager');
            $customer = $userManager->findUserBy(['id' => $request->get('id')]);

            if ($customer && $customer instanceof Customer) {
                $customer = ['name' => $customer->getCompany()];
            }

            return new JsonResponse($customer);
        }

        $vendor = new Vendor();
        $form = $this->createForm(
            $this->get('fs_udoras.vendor.create_form.type'),
            $vendor
        );

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @Route(path="/new/vendor/create", name="customer_create_vendor")
     * @Template("FSUdorasBundle:Customer/Customer:newVendor.html.twig")
     * @return array|Response
     * @throws \OutOfBoundsException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     */
    public function createVendorAction(Request $request)
    {
        $discriminator = $this->get('pugx_user.manager.user_discriminator');
        $discriminator->setClass('FS\UserBundle\Entity\Vendor');
        $userManager = $this->get('pugx_user_manager');

        /** @var Customer $vendor */
        $vendor = $userManager->createUser();
        $form = $this->createForm(
            $this->get('fs_udoras.vendor.create_form.type'),
            $vendor

        );

       // replace value of customer on Customer entity
        $vendorData = $request->get('fos_user_registration');
        if (array_key_exists('customer', $vendorData)) {
            $id = $vendorData['customer'];
            /** @var Customer $customer */
            $customer = $userManager->findUserBy(['id' => $id]);
            if($customer){
                $form->get('customer_name')->setData($customer->getCompany());
            }

        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $vendor->setPassword(uniqid('', true));
            $vendor->setPlainPassword(uniqid('', true));
            $vendor->setPasswordSetToken(md5(uniqid('', true)));
            $vendor->setEnabled(false);

            $userManager->updateUser($vendor, true);


            $this->get('fs.mailer')->sendLoginEmailMessage($vendor);

            return new JsonResponse(
                [
                    'show-modal-static',
                    $this->renderView('@FSUdoras/userCreated.html.twig')
                ]
            );
        }

        return new JsonResponse(['error',
            $this->renderView('@FSUdoras/Customer/Customer/newVendor_modal.html.twig', [
                'form' => $form->createView(),
            ])
        ]);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \RuntimeException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @Route(path="/reports/trainings", name="customer_reports_training_action")
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
            ->getAllTrainingStateByCustomerWithSoftDeletes($this->getUser());

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

        $this->get('session')->set('training-return', $this->generateUrl('customer_reports_training_action'));
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
     * @Route(path="/reports/trainings/pdf", name="customer_reports_training_pdf_action")
     */
    public function getReportTrainingsActionPDF(Request $request)
    {
        $form = $this->get('form.factory')
            ->create(new TrainingReportFilterType());

        $em = $this->getDoctrine()
            ->getManager();

        $trainingsReportsRaw = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getAllTrainingStateByCustomer($this->getUser());

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
}
