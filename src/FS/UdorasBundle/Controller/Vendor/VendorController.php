<?php

namespace FS\UdorasBundle\Controller\Vendor;

use FS\TrainingProgramsBundle\Form\Type\EmployeeTrainingStateFilterType;
use FS\UdorasBundle\Annotation\Resource;
use FS\UdorasBundle\Annotation\ResourceManipulation;
use FS\UdorasBundle\Form\Type\CreateVendorFormType;
use FS\UdorasBundle\Form\Type\UserFilterType;
use FS\UserBundle\Entity\Customer;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\Vendor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FS\UdorasBundle\Form\Type\CreateEmployeeType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VendorController
 * @package FS\UdorasBundle\Controller\Vendor
 * @author <vladislav@fora-soft.com>
 *
 * @Security(expression="is_granted('ROLE_VENDOR') || is_granted('ROLE_ADMIN')")
 */
class VendorController extends Controller
{
    /**
     * @param Request $request
     * @Route(path="", name="index_vendor")
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
        $form         = $this->get('form.factory')->create(new UserFilterType());
	    $repo         = $this->getDoctrine()->getRepository('FSUserBundle:Employee');
	    $employeesRaw = $repo->getFindAllByVendorRaw($this->getUser());

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $repo->addFilterConditions($form->get('query')->getData(), $employeesRaw, false);
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
     * @param Vendor $vendor
     * @Template()
     * @Route(path="/profile/{vendor}", name="index_vendor_profile")
     * @Security(expression="user.hasAccessTo(vendor)")
     * @return array
     */
    public function profileAction(Request $request, Vendor $vendor)
    {
        return [
            "vendor" => $vendor,
        ];
    }

    /**
     * @param Request $request
     * @param Vendor $vendor
     * @Template()
     * @Route(path="/profile/{vendor}/edit", name="vendor_edit_profile")
     * @Security(expression="user.hasAccessTo(vendor)")
     * @Resource(resource="vendor")
     * @return array
     */
    public function vendorEditProfileAction(Request $request, Vendor $vendor)
    {
        $form = $this->createForm($this->get('fs_udoras.vendor.create_form.type'), $vendor);

        if ($request->isXmlHttpRequest()) {
            $userManager = $this->get('fos_user.user_manager');
            $customer = $userManager->findUserBy(['id' => $request->get('id')]);

            if ($customer && $customer instanceof Customer) {
                $customer = ['name' => $customer->getFullName()];
            }

            return new JsonResponse($customer);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($vendor);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('index_vendor_profile', [
                    'vendor' => $vendor->getId(),
                ])
            );
        }

        return [
            "vendor" => $vendor,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return array|JsonResponse
     * @Route(path="/new/employee", name="vendor_new_employee")
     * @ResourceManipulation()
     * @Template()
     */
    public function newEmployeeAction(Request $request)
    {
        // for autocomplete Vendor name in employee creation form
        if ($request->isXmlHttpRequest()) {
            $userManager = $this->get('fos_user.user_manager');
            $vendor = $userManager->findUserBy(['id' => $request->get('id')]);

            if ($vendor && $vendor instanceof Vendor) {
                $vendor = ['name' => $vendor->getFullName()];
            }

            return new JsonResponse($vendor);
        }

        $employee = new Employee();
        $form = $this->createForm('fs_user_create_employee_form', $employee);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return array|Response
     * @Route(path="/new/employee/create", name="vendor_create_employee")
     * @Template("FSUdorasBundle:Vendor/Vendor:newEmployee.html.twig")
     */
    public function createEmployeeAction(Request $request)
    {
        $discriminator = $this->get('pugx_user.manager.user_discriminator');
        $discriminator->setClass('FS\UserBundle\Entity\Employee');

        $userManager = $this->get('pugx_user_manager');

        /** @var Employee $employee */
        $employee = $userManager->createUser();
        $form = $this->createForm('fs_user_create_employee_form', $employee);
        $eData = $request->get('fos_user_registration');
        if (array_key_exists('vendor', $eData)) {
            $id = $eData['vendor'];
            /** @var Customer $customer */
            $vendor = $userManager->findUserBy(['id' => $id]);
            if($vendor)
                $form->get('vendor_name')->setData($vendor->getFullName());
        }
        $form->handleRequest($request);

        if ($form->isValid()) {
            $employee->setPassword(uniqid('', true));
            $employee->setPlainPassword(uniqid('', true));
            $employee->setPasswordSetToken(md5(uniqid('', true)));
            $employee->setEnabled(false);

            $userManager->updateUser($employee, true);

            $this->get('fs.mailer')->sendLoginEmailMessage($employee);

            return new JsonResponse(
                [
                    'show-modal-static',
                    $this->renderView('@FSUdoras/userCreated.html.twig')
                ]
            );
        }

        return new JsonResponse(['error',
            $this->renderView('@FSUdoras/Vendor/Vendor/newEmployee_modal.html.twig', [
                'form' => $form->createView(),
            ])
        ]);
    }
}
