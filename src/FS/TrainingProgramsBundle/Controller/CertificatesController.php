<?php

namespace FS\TrainingProgramsBundle\Controller;


use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use FS\TrainingProgramsBundle\Util\EmailVerifier;
use Symfony\Component\Security\Csrf\CsrfToken;


class CertificatesController extends Controller
{
    /**
     * @param Request $request
     * @param EmployeeTrainingState $employeeTrainingState
     * @return Response
     * @Route(
     *     path="/employee/certificates/{employeeTrainingState}",
     *     name="employee_certificate"
     * )
     * @Security(expression="user.hasAccessTo(employeeTrainingState.getAccess().getEmployee())")
     * @Template()
     */
    public function showAction(Request $request, EmployeeTrainingState $employeeTrainingState)
    {
        if ($employeeTrainingState->getPassedStatus() != EmployeeTrainingState::FINAL_STATUS_PASSED) {
            throw new NotFoundHttpException('Certificate not found');
        }

        return [
            'trainingState' => $employeeTrainingState,
            'referrer' => $request->headers->get('referer')
        ];
    }

    /**
     * @param int $trainingStateId
     * @return Response
     * @Route(
     *     path="/employee/certificates/{trainingStateId}/download",
     *     name="employee_certificate_download"
     * )
     */
    public function downloadAction($trainingStateId)
    {
        $trainingState = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->findTrainingStateWithEmployeeAndCustomerByIdWithSoftDeleted($trainingStateId);

        $this->isCertificateAvailableFor($trainingState);

        $knpSnappyPDF = $this->get('knp_snappy.pdf');
        $certificateHtml = $this->renderView('@FSTrainingPrograms/Certificates/certificateTemplate.html.twig', [
            'trainingState' => $trainingState
        ]);

        return new Response(
            $knpSnappyPDF->getOutputFromHtml($certificateHtml, [
                'encoding' => 'utf-8',
                'page-size' => 'A3',
            ]),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="certificate.pdf"'
            ]
        );
    }

    /**
     * @param int $trainingStateId
     * @return Response
     * @Route(
     *     path="/employee/certificates/{trainingStateId}/image",
     *     name="employee_certificate_image"
     * )
     */
    public function certificateImageAction($trainingStateId)
    {
        $trainingState = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->findTrainingStateWithEmployeeAndCustomerByIdWithSoftDeleted($trainingStateId);

        $this->isCertificateAvailableFor($trainingState);

        $knpSnappyIMG = $this->get('knp_snappy.image');
        $certificateHtml = $this->renderView('@FSTrainingPrograms/Certificates/certificateTemplate.html.twig', [
            'trainingState' => $trainingState
        ]);

        return new Response(
            $knpSnappyIMG->getOutputFromHtml($certificateHtml, [
                'encoding' => 'utf-8',
            ]),
            200,
            [
                'Content-Type' => 'image/jpg'
            ]
        );
    }

    /**
     * @param Request $request
     * @param int $trainingStateId
     * @return Response
     * @Route(
     *     path="/employee/certificates/{trainingStateId}/share",
     *     name="employee_certificate_share"
     * )
     */
    public function shareCertificateAction(Request $request, $trainingStateId)
    {
        $trainingState = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->findTrainingStateWithEmployeeAndCustomerByIdWithSoftDeleted($trainingStateId);

        $this->isCertificateAvailableFor($trainingState);

        if ($request->isXmlHttpRequest()) {
            if ($request->isMethod('GET')) {
                return $this->render('@FSTrainingPrograms/Certificates/shareCertificate.html.twig', [
                    'trainingState' => $trainingState
                ]);
            } else {
                $emails = EmailVerifier::filterValidEmails($request->get('email'), $this->get('validator'));
                $token = new CsrfToken(
                    $trainingStateId . $trainingState->getTraining()->getTitle(),
                    $request->get('token')
                );

                if (count($emails) > 0 && $this->get('security.csrf.token_manager')->isTokenValid($token) ) {
                    $mailer = $this->get('fs.mailer');
                    $knpSnappyPDF = $this->get('knp_snappy.pdf');
                    $certificateHtml = $this->renderView('@FSTrainingPrograms/Certificates/certificateTemplate.html.twig', [
                        'trainingState' => $trainingState
                    ]);
                    $PDFData = $knpSnappyPDF->getOutputFromHtml($certificateHtml, [
                        'encoding' => 'utf-8',
                        'page-size' => 'A3',
                    ]);

                    foreach ($emails as $email) {
                        $mailer->sendShareCertificateMessage($email, $PDFData);
                    }

                    return new JsonResponse([
                        'replace',
                        $this->renderView('@FSTrainingPrograms/Certificates/shareCertificateSuccess.html.twig', [
                            'text' => count($emails) > 1 ? 'Emails' : 'Email'
                        ])
                    ]);
                }

                return new JsonResponse([
                    'replace',
                    $this->renderView('@FSTrainingPrograms/Certificates/shareCertificateError.html.twig')
                ]);
            }
        }

        return $this->redirectToRoute('employee_certificate', [
            'employeeTrainingState' => $trainingStateId
        ]);
    }

    /**
     * Check is certificate available for certain User and certain TrainingState
     *
     * @param EmployeeTrainingState $trainingState
     */
    private function isCertificateAvailableFor(EmployeeTrainingState $trainingState)
    {
        if (
            is_null($trainingState)
            || $trainingState->getPassedStatus() != EmployeeTrainingState::FINAL_STATUS_PASSED
            || !$this->getUser()->hasAccessTo($trainingState->getAccess()->getEmployee())
        ) {
            throw new NotFoundHttpException('Certificate Image not found');
        }
    }
}