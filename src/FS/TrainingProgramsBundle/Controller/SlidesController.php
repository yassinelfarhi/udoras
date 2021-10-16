<?php

namespace FS\TrainingProgramsBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\EncodingQueueItem;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UserBundle\Entity\Employee;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SlidesController extends Controller
{
    /**
     * @param TrainingProgram $trainingProgram , Slide $slide|null
     * @return array
     * @throws \LogicException
     * @Route(path="/training-programs/{link}/training/{slide}", name="show_training_program_slide")
     * @ParamConverter("trainingProgram", class="FSTrainingProgramsBundle:TrainingProgram", options={"link" = "link"})
     * @Security(
     *     expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && (user.hasRole('ROLE_ADMIN') || user.hasRole('ROLE_CUSTOMER'))"
     * )
     * @Template()
     */
    public function showAction(TrainingProgram $trainingProgram, Slide $slide = null)
    {
        $user = $this->getUser();

        if ($user->hasRole('ROLE_CUSTOMER') && $user != $trainingProgram->getCustomer()) {
            return $this->render('FSTrainingProgramsBundle:TrainingPrograms:trainingProgramAccessError.html.twig');
        }

        $slideRepository = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:Slide');

        if (is_null($slide)) {
            $slide = $slideRepository->getSlideByTrainingProgramAndRealNum($trainingProgram);

            if (is_null($slide)) {
                return $this->redirectToRoute('show_training_program', ['link' => $trainingProgram->getLink()]);
            }
        }

        $nextSlide = $slideRepository->getSlideByTrainingProgramAndRealNum(
            $trainingProgram,
            (int)$slide->getRealNum() + 1
        );
        $numberOfSlides = $slideRepository->countAllSlidesOfTrainingProgram($trainingProgram);

        if ($hasLastTest = $trainingProgram->hasQuestions()) {
            ++$numberOfSlides;
        }

        return [
            'trainingProgram' => $trainingProgram,
            'slide' => $slide,
            'nextSlide' => $nextSlide,
            'numberOfSlides' => $numberOfSlides,
            'hasLastTest' => $hasLastTest
        ];
    }

    /**
     * @param TrainingProgram $trainingProgram
     * @return array
     * @throws \LogicException
     * @Route(path="/training-programs/{link}/test", name="training_program_test")
     * @ParamConverter("trainingProgram", class="FSTrainingProgramsBundle:TrainingProgram", options={"link" = "link"})
     * @Security(
     *     expression="(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')) && ((user.hasRole('ROLE_ADMIN') || trainingProgram.getCustomer() == user) && trainingProgram.hasQuestions())"
     * )
     * @Template()
     */
    public function testAction(TrainingProgram $trainingProgram)
    {
        $slideRepository = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:Slide');

        $questions = $slideRepository->getAllSlidesWithQuestionsByTrainingProgram($trainingProgram);

        $numberOfSlides = $slideRepository->countAllSlidesOfTrainingProgram($trainingProgram);

        if ($hasLastTest = $trainingProgram->hasQuestions()) {
            ++$numberOfSlides;
        }

        return [
            'trainingProgram' => $trainingProgram,
            'numberOfSlides' => $numberOfSlides,
            'questions' => $questions
        ];
    }

    /**
     * @param Request $request
     * @param TrainingProgram $trainingProgram
     * @return array
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Route(path="/employee/program/{link}", name="show_training_program_employee")
     * @ParamConverter("trainingProgram", class="FSTrainingProgramsBundle:TrainingProgram", options={"link" = "link"})
     * @Security(
     *     expression="user.hasRole('ROLE_EMPLOYEE')"
     * )
     * @Template()
     *
     * Need to add check for available this training for user
     */
    public function employeeTrainingAction(Request $request, TrainingProgram $trainingProgram)
    {
        /** @var Employee $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()
            ->getManager();

        $employeeTS = $em->getRepository('FSTrainingProgramsBundle:EmployeeTrainingState')
            ->getTrainingStateByEmployeeAndProgram($user, $trainingProgram);

        if ($employeeTS === null) {
            return new RedirectResponse(
                $this->generateUrl('index_employee')
            );
        }
        $slideRepository = $this->getDoctrine()->getRepository('FSTrainingProgramsBundle:Slide');

        $numberOfSlides = $slideRepository->countAllSlidesOfTrainingProgram($trainingProgram);

        if ($hasLastTest = $trainingProgram->hasQuestions()) {
            ++$numberOfSlides;
        }
        return [
            'trainingProgram' => $trainingProgram,
            'trainingState' => $employeeTS,
            'slidesNumber' => $numberOfSlides,
        ];
    }


    /**
     * @param Request $request
     * @param EmployeeTrainingState $trainingState
     * @Route(
     *     path="/employee/manage-slide/{trainingState}",
     *     name="training_state_manage_slide",
     *     options = { "expose" = true }
     * )
     * @Security(
     *     expression="user.hasRole('ROLE_EMPLOYEE')"
     * )
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function presentationSlideAction(Request $request, EmployeeTrainingState $trainingState)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $cmd = $request->request->get('command', 'nothing');

        if ($trainingState->getPaused()) {
            $cmd = 'continue';
        }

        /** @var Slide $slide */
        $slide = $this->get('fs.training_state.factory')
            ->factory(
                $cmd,
                $trainingState
            )
            ->execute();


        $final = false;
        $hidePrevButton = false;

        $trainingProgram = $trainingState->getTraining();
        $slideRepository = $em->getRepository('FSTrainingProgramsBundle:Slide');
        $numberOfSlides = $slideRepository->countAllSlidesOfTrainingProgram($trainingProgram);

        if ($hasLastTest = $trainingProgram->hasQuestions()) {
            ++$numberOfSlides;
        }

        if ((int)$slide->getRealNum() === (int)$numberOfSlides) {
            $final = true;
        }

        if ((int)$slide->getRealNum() === 1 || (isset($slide->getExtraFields()['hidePrevButton']))) {
            $hidePrevButton = true;
        }

        $prevSlide = $slideRepository->getSlideByTrainingProgramAndRealNum(
            $trainingState->getTraining(),
            (int)$slide->getRealNum() - 1
        );

        if (empty($prevSlide)
            || $prevSlide->getSlideType() === Slide::SLIDE_TYPE__QUESTION
            || $prevSlide->getSlideType() === Slide::SLIDE_TYPE__ANSWERED_QUESTION
        ) {
            $hidePrevButton = true;
        }

        switch ($slide->getSlideType()) {
            case Slide::SLIDE_TYPE__AUDIO:
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/audio.html.twig', ['slide' => $slide]);
                break;
            case Slide::SLIDE_TYPE__VIDEO:
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/video.html.twig', ['slide' => $slide]);
                break;
            case Slide::SLIDE_TYPE__IMAGE:
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/image.html.twig', ['slide' => $slide]);
                break;
            case Slide::SLIDE_TYPE__TEXT:
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/text.html.twig', ['slide' => $slide]);
                break;
            case Slide::SLIDE_TYPE__QUESTION:
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/question.html.twig', ['slide' => $slide]);
                break;
            case Slide::SLIDE_TYPE__ANSWERED_QUESTION:
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/ansQuestion.html.twig', ['slide' => $slide]);
                break;
            case Slide::SLIDE_TYPE_FINAL_QUESTION:
                /** @var ArrayCollection $questions */
                $questions = $trainingProgram->getQuestionsSlides();
                $questions = $questions->toArray();
                $timeLimit = 0;
                /** @var Slide $question */
                foreach ($questions as $question) {
                    $timeLimit += $question->getTimeLimit();
                }
                $slide->setTimeLimit($timeLimit);
                /** @var array $questions */
                shuffle($questions);
                $html = $this->renderView('@FSTrainingPrograms/Slides/slides/finalQuestion.html.twig', [
                        'questions' => $questions,
                    ]
                );
                $hidePrevButton = true;

                break;
            case Slide::SLIDE_TYPE__NO_SLIDE:
                if ($trainingProgram->getQuestionsSlides()->count() === 0) {
                    $trainingState->setPassedStatus(EmployeeTrainingState::FINAL_STATUS_PASSED);
                    $trainingState->setRatio(100);
                    $trainingState->setEndTime(new \DateTime());
                    $trainingState->generateCertificateId();
                    $trainingState->fillValidUntil();
                    $trainingState->setTimeOffset($request->request->get('offset', 0));
                    $em->persist($trainingState);
                    $em->flush();
                }
            // no break
            case Slide::SLIDE_TYPE__REDIRECT:
                return new JsonResponse([
                    'op' => 'redirect',
                    'target' => $this->generateUrl(
                        'training_program_result_show', [
                            'link' => $trainingProgram->getLink()
                        ]
                    ),
                ]);
                break;
            case Slide::SLIDE_TYPE__BLANK:
            default:
                $html = '';
                break;
        }

        if ($slide->getTimeLimit() > 0) {
            if ($trainingState->getStartTimer() === null && $trainingState->getEndTimer() === null) {
                $dt = new \DateTime();
                $trainingState->setStartTimer($dt);
                $edt = clone $dt;
                $edt->setTimestamp($dt->getTimestamp() + $slide->getTimeLimit());
                $trainingState->setEndTimer($edt);
                $trainingState->setTimeRemaining($edt->getTimestamp() - $dt->getTimestamp());
            } else {
                $now = new \DateTime();
                $trainingState->setTimeRemaining(
                    $trainingState->getEndTimer()->getTimestamp() - $now->getTimestamp()
                );
            }
        } else {
            $trainingState->setTimeRemaining(0);
        }

        $em->persist($trainingState);
        $em->flush();
        $serializer = $this->get('serializer');

        $slide = $serializer->serialize(
            $slide,
            'json',
            [
                'groups' => ['training']
            ]
        );

        return new JsonResponse([
            'op' => 'next_slide',
            'slide' => json_decode($slide, true),
            'nextSlide' => $trainingState->getNextSlide(),
            'final' => $final,
            'hidePrev' => $hidePrevButton,
            'timeRemaining' => $trainingState->getTimeRemaining(),
            'html' => $html,
        ]);
    }

    /**
     * @Route(
     *     path="/encoding-status/{item}",
     *     name="get_encoding_status",
     *     options = { "expose" = true }
     * )
     */
    public function getEncodingStatus(EncodingQueueItem $item)
    {
        return new JsonResponse([
            'path' => $item->getUrl(),
            'status' => $item->getEncodingStatus()
        ]);
    }
}