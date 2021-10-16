<?php

namespace FS\TrainingProgramsBundle\Controller;


use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;


class PaymentsController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @Route(path="/admin/payments" , name="payments")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $paymentsRaw = $this->getDoctrine()
            ->getRepository('FSTrainingProgramsBundle:Payment')
            ->getFindAllWithDeletedOrderedByDateRaw();
        $paymentsPagination = $this->get('knp_paginator')
            ->paginate($paymentsRaw, $request->query->get('page', 1), 30);

        $this->get('session')->set('training-return', $this->generateUrl('payments'));

        return $this->render('FSTrainingProgramsBundle:Payments:index.html.twig', [
            'paymentsPagination' => $paymentsPagination
        ]);
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @throws
     * @return Response
     * @Route(path="/training-programs/{link}/pay", name="pay_for_training")
     * @ParamConverter("trainingProgram", class="FSTrainingProgramsBundle:TrainingProgram")
     * @Security(
     *     expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && (user.hasRole('ROLE_VENDOR') || user.hasRole('ROLE_EMPLOYEE'))"
     * )
     */
    public function payAction(Request $request, TrainingProgram $trainingProgram)
    {
        $token = $request->get('stripeToken');
        $amount = $request->get('amount') > 0 ? $request->get('amount') : 1;
        $paymentDone = false;

        if ($token) {
            $stripeService = $this->get('fs_training.util.stripe_service');
            
            if ($this->getUser()->hasRole('ROLE_VENDOR')) {
                $requestManager = $this->get('fs_training.model.manager.request_manager');
                $trainingRequest = $requestManager->getRequest($this->getUser(), $trainingProgram);
                $paymentDone = $stripeService->buyTrainingsAsVendor($this->getUser(), $trainingRequest, $token, $amount);
            } else {
                $accessManager = $this->get('fs_training.model.manager.access_manager');
                $trainingAccess = $accessManager->getAccess($this->getUser(), $trainingProgram);
                $paymentDone = $stripeService->buyTrainingAsEmployee($this->getUser(), $trainingAccess, $token);
            }
        }

        if ($request->isXmlHttpRequest()) {
            $responseJsonData = null;

            if ($paymentDone == false) {
                $responseJsonData = [
                    'status' => 'success',
                    'content' => $this->renderView('@FSTrainingPrograms/Payments/success.html.twig', [
                        'amount' => $amount
                    ])
                ];
            } elseif ($this->getUser()->hasRole('ROLE_VENDOR')) {
                $responseJsonData = [
                    'status' => 'success-vendor',
                    'content' => [
                        'trainingStatus' => $trainingRequest->getStatus(),
                        'modal' => $this->renderView('@FSTrainingPrograms/Payments/successVendor.html.twig', [
                            'amount' => $trainingRequest->getAmountOfTrainings()
                        ])
                    ]
                ];
            } else {
                $responseJsonData = [
                    'status' => 'success-employee',
                    'content' => [
                        'modal' => $this->renderView('@FSTrainingPrograms/Payments/successEmployee.twig'),
                        'row' => $this->renderView('@FSTrainingPrograms/Requests/employee/requestRow.html.twig', [
                            'trainingProgram' => $trainingProgram,
                            'access' => $trainingAccess,
                            'trainingState' => $trainingAccess->getTrainingState()
                        ]),
                        'trainingAction' => $this->renderView(
                            '@FSTrainingPrograms/TrainingPrograms/employee/trainingAction.html.twig',
                            [
                                'trainingProgram' => $trainingProgram,
                                'access' => $trainingAccess,
                            ]
                        )
                    ]
                ];
            }

            return new JsonResponse($responseJsonData);
        }

        return $this->redirectToRoute('show_training_program', [
            'link' => $trainingProgram->getLink()
        ]);
    }
}