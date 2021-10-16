<?php

namespace FS\TrainingProgramsBundle\Controller;


use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\Request as TrainingRequest;
use FS\UserBundle\Entity\Vendor;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FS\TrainingProgramsBundle\Form\Type\TrainingProgramFilterType;


class RequestsController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @Route(
     *     path="/vendor/training-requests/",
     *     name="fs_trainings_requests_show_vendor_training_requests"
     * )
     */
    public function showVendorTrainingRequestsAction(Request $request)
    {
        $form = $this->get('form.factory')->create(new TrainingProgramFilterType());
        $trainingProgramsRaw = $this->getDoctrine()
            ->getRepository('FSTrainingProgramsBundle:TrainingProgram')
            ->getFindAllByVendorQuery($this->getUser());
            
        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $trainingProgramsRaw);
        }
        
        $trainingProgramPagination = $this->get('knp_paginator')
            ->paginate($trainingProgramsRaw, $request->query->get('page', 1), 30);

        return $this->render('FSTrainingProgramsBundle:Requests/vendor:show.html.twig', [
            'form' => $form->createView(),
            'trainingProgramPagination' => $trainingProgramPagination
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(
     *     path="/employee/",
     *     name="index_employee"
     * )
     */
    public function showEmployeeTrainingAccessesAction(Request $request)
    {
        $form = $this->get('form.factory')->create(new TrainingProgramFilterType());
        $trainingProgramsRaw = $this->getDoctrine()
            ->getRepository('FSTrainingProgramsBundle:TrainingProgram')
            ->getFindAllByEmployeeQuery($this->getUser());

        if ($request->query->has($form->getName())) {
            $form->submit($request->query->get($form->getName()));
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $trainingProgramsRaw);
        }

        $trainingProgramPagination = $this->get('knp_paginator')
            ->paginate($trainingProgramsRaw, $request->query->get('page', 1), 30);

        return $this->render('FSTrainingProgramsBundle:Requests/employee:show.html.twig', [
            'form' => $form->createView(),
            'trainingProgramPagination' => $trainingProgramPagination
        ]);
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @return Response
     * @Route(
     *     path="/training-program/{trainingProgram}/send-request/show-modal",
     *     name="fs_training_programs_requests_show_vendors"
     * )
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_CUSTOMER')")
     * @Template()
     */
    public function showVendorsAction(Request $request, TrainingProgram $trainingProgram)
    {
        $user = $this->getUser();
        $employees = null;

        if ($user->hasRole('ROLE_CUSTOMER')) {
            $employeesRepository = $this->getDoctrine()->getRepository('FSUserBundle:Employee');
            $employees = $employeesRepository->findByCustomerWithoutTrainingProgram($user, $trainingProgram);
        }

        return [
            'trainingProgram' => $trainingProgram,
            'customer' => $user,
            'employees' => $employees
        ];
    }

    /**
     * @param TrainingProgram $trainingProgram
     * @param Vendor|null $vendor
     * @return Response
     * @Route(
     *     path="/training-program/{trainingProgram}/send-request/employees/{vendor}",
     *     name="requests_vendor"
     * )
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && (user.hasRole('ROLE_CUSTOMER') || user.hasRole('ROLE_VENDOR'))")
     */
    public function updateEmployeesAction(TrainingProgram $trainingProgram, Vendor $vendor = null)
    {
        $user = $this->getUser();
        $employees = null;
        $employeesRepository = $this->getDoctrine()->getRepository('FSUserBundle:Employee');

        if (is_null($vendor) && $user->hasRole('ROLE_CUSTOMER')) {
            $employees = $employeesRepository
                ->findByCustomerWithoutTrainingProgram($this->getUser(), $trainingProgram);
        } else {
            $employees = $employeesRepository
                ->findByVendorWithOutTrainingProgram($vendor, $trainingProgram);
        }

        return $this->render('FSTrainingProgramsBundle:Requests:updateEmployees.html.twig', [
            'trainingProgram' => $trainingProgram,
            'employees' => $employees
        ]);
    }

    /**
     * @param TrainingProgram $trainingProgram
     * @return Response
     * @Route(
     *     path="/training-program/{trainingProgram}/send-request/employees/",
     *     name="fs_training_programs_requests_show_employees"
     * )
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_VENDOR')")
     * @Template()
     */
    public function showEmployeesAction(TrainingProgram $trainingProgram)
    {
        $employees = $this->getDoctrine()
            ->getRepository('FSUserBundle:Employee')
            ->findByVendorWithOutTrainingProgram($this->getUser(), $trainingProgram);

        return [
            'trainingProgram' => $trainingProgram,
            'employees' => $employees
        ];
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @return Response
     * @Route(
     *     path="/training-program/{trainingProgram}/training-requests/create",
     *     name="fs_training_programs_requests_create"
     * )
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_CUSTOMER')")
     */
    public function createAction(Request $request, TrainingProgram $trainingProgram)
    {
        $vendorRepository = $this->getDoctrine()->getRepository('FSUserBundle:Vendor');
        $employeeRepository = $this->getDoctrine()->getRepository('FSUserBundle:Employee');

        $vendors = $vendorRepository->findBy(['id' => $request->get('vendors')]);
        $employees = $employeeRepository->findBy(['id' => $request->get('employees')]);

        if (empty($vendors) && empty($employees)) {
            throw new HttpException(400, 'Invalid data');
        }

        $mailer = $this->get('fs.mailer');
        $requestManager = $this->get('fs_training.model.manager.request_manager');
        $accessManager = $this->get('fs_training.model.manager.access_manager');

        $requestManager->createRequests($vendors, $trainingProgram, $mailer);
        $accessManager->createAccesses($employees, $trainingProgram, null, $mailer);

        return $this->render('FSTrainingProgramsBundle:Requests:requestsSended.html.twig');
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @return Response
     * @Route(
     *     path="/training-program/{trainingProgram}/training-accesses/create",
     *     name="fs_training_programs_requests_create_employees_accesses"
     * )
     * @Security(expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_VENDOR')")
     */
    public function createEmployeeAccessesAction(Request $request, TrainingProgram $trainingProgram)
    {
        $employeeRepository = $this->getDoctrine()->getRepository('FSUserBundle:Employee');
        $employees = $employeeRepository->findBy(['id' => $request->get('employees')]);

        if (empty($employees)) {
            throw new HttpException(400, 'Invalid data');
        }

        $requestRepository = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:Request');
        $request = $requestRepository->findOneBy([
            'trainingProgram' => $trainingProgram,
            'vendor' => $this->getUser(),
        ]);

        $mailer = $this->get('fs.mailer');
        $accessManager = $this->get('fs_training.model.manager.access_manager');
        $accessManager->createAccesses($employees, $trainingProgram, $request, $mailer);

        return $this->render('FSTrainingProgramsBundle:Requests:requestsSended.html.twig');
    }
}