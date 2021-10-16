<?php

namespace FS\UdorasBundle\Controller\Employee;

use FS\TrainingProgramsBundle\Form\Type\EmployeeTrainingStateFilterType;
use FS\UdorasBundle\Annotation\Resource;
use FS\UdorasBundle\Form\Type\CreateEmployeeType;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\Vendor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EmployeeController
 * @package FS\UdorasBundle\Controller\Employee
 * @author <vladislav@fora-soft.com>
 *
 * @Security(expression="is_granted('ROLE_EMPLOYEE') || is_granted('ROLE_ADMIN')")
 */
class EmployeeController extends Controller
{
    /**
     * @param Request $request
     * @param Employee $employee
     * @Template()
     * @Route(path="/profile/{employee}", name="index_employee_profile")
     * @Security(
     *     expression="user.hasAccessTo(employee)"
     * )
     * @return array
     * @throws \LogicException
     */
    public function profileAction(Request $request, Employee $employee)
    {
        $accessPagination = null;

        return [
            "employee" => $employee,
        ];
    }


    /**
     * @param Request $request
     * @param Employee $employee
     * @Template()
     * @Route(path="/profile/{employee}/edit", name="employee_edit_profile")
     * @Security(
     *     expression="user.hasAccessTo(employee)"
     * )
     * @Resource(resource="employee")
     * @return array
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function employeeEditProfileAction(Request $request, Employee $employee)
    {
        if ($request->isXmlHttpRequest()) {
            $userManager = $this->get('fos_user.user_manager');
            $vendor = $userManager->findUserBy(['id' => $request->get('id')]);

            if ($vendor && $vendor instanceof Vendor) {
                $vendor = ['name' => $vendor->getFullName()];
            }

            return new JsonResponse($vendor);
        }

        $form = $this->createForm('fs_user_create_employee_form', $employee);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($employee);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('index_employee_profile',
                    [
                        'employee' => $employee->getId(),
                    ]
                )
            );
        }

        return [
            "employee" => $employee,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @param Employee $employee
     * @return array
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \RuntimeException
     * @throws \LogicException
     * @Route(path="/{employee}/certificates", name="index_employee_certificate")
     * @Security(expression="user.hasAccessTo(employee)")
     *
     * @Template()
     */
    public function viewEmployeeCertificatesAction(Request $request, Employee $employee)
    {
        $form = $this->get('form.factory')
            ->create(new EmployeeTrainingStateFilterType());

        $em = $this->getDoctrine()
            ->getManager();

        $trainingProgramsCompletedRaw = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getAllTrainingStateFinishedByEmployeeWithSoftDeletedQueryBuilder($employee);

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $trainingProgramsCompletedRaw);
        }

        $q = $trainingProgramsCompletedRaw->getQuery()->getResult();
        //$q = array_unique($q);

        $paginator = $this->get('knp_paginator')->paginate($q, $request->query->get('page', 1), 30);

        return [
            'employee' => $employee,
            'paginator' => $paginator,
            'form' => $form->createView(),
        ];
    }
}
