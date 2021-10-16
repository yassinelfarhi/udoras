<?php

namespace FS\TrainingProgramsBundle\Controller;


use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FS\TrainingProgramsBundle\Entity\Link;
use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Symfony\Component\HttpFoundation\JsonResponse;


class BookTrainingsController extends Controller
{
    /**
     * @param Link $link
     * @return Response
     * @Route(
     *     path="/training-program/free-access/{link}/add-training",
     *     name="training_program_free_access_add"
     * )
     * @ParamConverter("link", class="FSTrainingProgramsBundle:Link", options={"mapping": {"link": "link"}})
     * @Security(
     *     expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_EMPLOYEE')"
     * )
     */
    public function bookFreeTrainingAction(Link $link) {
        if ($link->getTrainings() <= $link->getTrainingsUsed()) {
            return new JsonResponse([
                'error',
                'modal' => $this->renderView('@FSTrainingPrograms/Links/expiredLinkError.twig')
            ]);
        }

        $accessManager = $this->get('fs_training.model.manager.access_manager');
        $access = $accessManager->createAccessIfNotExists($this->getUser(), $link->getTrainingProgram());

        // redirect to training if user already has access to this training
        if ($access->getTrainingState()) {
            return new JsonResponse([
                'redirect',
                $this->generateUrl('show_training_program', ['link'=> $link->getTrainingProgram()->getLink()])
            ]);
        }

        $link->setTrainingsUsed($link->getTrainingsUsed() + 1);
        $accessManager->addNewTrainingStateToAccessAndUpdateState($access, Access::FREE);

        return new JsonResponse([
            'redirect',
            $this->generateUrl('show_training_program', ['link'=> $link->getTrainingProgram()->getLink()])
        ]);
    }

    /**
     * @param TrainingProgram $trainingProgram
     * @return Response
     * @Route(
     *     path="/training-program/{trainingProgram}/add-training",
     *     name="add_training_program_bought_by_vendor"
     * )
     * @Security(
     *     expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && user.hasRole('ROLE_EMPLOYEE')"
     * )
     */
    public function bookTrainingBoughtByVendorAction(TrainingProgram $trainingProgram)
    {
        $employee = $this->getUser();
        $accessManager = $this->get('fs_training.model.manager.access_manager');
        $access = $accessManager->getAccess($employee, $trainingProgram);

        try {
            $accessManager->bookTrainingBoughtByVendor($access);
        } catch (NotFoundHttpException $exception) {
            return new JsonResponse([
                'status' => 'redirect',
                'content' => $this->generateUrl('index_employee')
            ]);
        } catch (AccessDeniedException $exception) {
            return new JsonResponse([
                'status' => 'error',
                'content' => $this->renderView('@FSTrainingPrograms/bookTrainings/error.html.twig'),
                'row' => $this->renderView('@FSTrainingPrograms/Requests/employee/requestRow.html.twig', [
                    'trainingProgram' => $trainingProgram,
                    'access' => $access,
                    'trainingState' => $access->getTrainingState(),
                ]),
                'trainingAction' => $this->renderView(
                    '@FSTrainingPrograms/TrainingPrograms/employee/trainingAction.html.twig',
                    [
                        'trainingProgram' => $trainingProgram,
                        'access' => $access,
                    ]
                )
            ]);
        }

        return new JsonResponse([
            'status' => 'success',
            'content' => $this->renderView('@FSTrainingPrograms/bookTrainings/success.html.twig', [
                'trainingProgram' => $trainingProgram
            ]),
            'row' => $this->renderView('@FSTrainingPrograms/Requests/employee/requestRow.html.twig', [
                'trainingProgram' => $trainingProgram,
                'access' => $access,
                'trainingState' => $access->getTrainingState()
            ]),
            'trainingAction' => $this->renderView(
                '@FSTrainingPrograms/TrainingPrograms/employee/trainingAction.html.twig',
                [
                    'trainingProgram' => $trainingProgram,
                    'access' => $access,
                ]
            )
        ]);
    }
}