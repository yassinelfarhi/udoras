<?php

namespace FS\TrainingProgramsBundle\Controller;


use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\Link;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use FS\TrainingProgramsBundle\Form\Type\LinkType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class LinksController extends Controller
{
    /**
     * @Route(path="/admin/training-programs/{trainingProgram}/links/new", name="admin_add_link_to_training_program")
     * @Security(expression="user.hasRole('ROLE_ADMIN') || user == trainingProgram.getCustomer()")
     */
    public function createAction(Request $request, TrainingProgram $trainingProgram)
    {
        $link = new Link();
        $form = $this->createForm(new LinkType(), $link);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $link->setLink(uniqid('', true));
            $link->setTrainingProgram($trainingProgram);

            $em->persist($link);
            $em->flush();

            $linksQuery = $this->getDoctrine()
                ->getRepository('FSTrainingProgramsBundle:Link')
                ->getFindAllByTrainingProgramQuery($trainingProgram);
            $linksPagination = $this->get('knp_paginator')->paginate($linksQuery, $request->query->get('page', 1), 30);
            $linksPagination->setUsedRoute('admin_training_program_edit');

            return new JsonResponse([
                'success',
                $this->renderView('FSTrainingProgramsBundle:Links:links.html.twig', [
                    'trainingProgram' => $trainingProgram,
                    'linksPagination' => $linksPagination,
                    'edit' => true
                ])
            ]);
        }

        return new JsonResponse([
            'error',
            $this->renderView('FSTrainingProgramsBundle:Links:new_link.html.twig', [
                'form' => $form->createView(),
                'trainingProgram' => $trainingProgram
            ])
        ]);
    }

    /**
     * @Route("/admin/training-programs/{trainingProgram}/links/{link}/confirm", name="admin_link_delete_confirmation")
     * @Security(expression="user.hasRole('ROLE_ADMIN')")
     * @Template()
     */
    public function confirmDeleteAction(TrainingProgram $trainingProgram, Link $link)
    {
        return [
            'trainingProgram' => $trainingProgram,
            'link' => $link
        ];
    }

    /**
     * @Route(
     *     path="/admin/training-programs/{trainingProgram}/links/{link}/delete",
     *     name="admin_delete_training_program_link"
     * )
     * @Security(expression="user.hasRole('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, TrainingProgram $trainingProgram, Link $link)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($link);
        $em->flush();

        $linksQuery = $this->getDoctrine()
            ->getRepository('FSTrainingProgramsBundle:Link')
            ->getFindAllByTrainingProgramQuery($trainingProgram);
        $linksPagination = $this->get('knp_paginator')->paginate($linksQuery, $request->query->get('page', 1), 30);
        $linksPagination->setUsedRoute('admin_training_program_edit');

        return new JsonResponse([
            'success',
            $this->renderView('FSTrainingProgramsBundle:Links:links.html.twig', [
                'trainingProgram' => $trainingProgram,
                'linksPagination' => $linksPagination,
                'edit' => true
            ])
        ]);

    }

    /**
     * @param Link $link
     * @return Response
     * @throws \InvalidArgumentException
     * @Route(path="/training-program/free-access/{link}", name="training_program_free_access")
     * @ParamConverter("link", class="FSTrainingProgramsBundle:Link", options={"mapping": {"link": "link"}})
     *
     * @Template()
     */
    public function freeLinkAccessAction(Link $link)
    {
        $user = $this->getUser();

        if(!$user){
            $session = $this->get('session');
            $session->set('next-page', $this->generateUrl('training_program_free_access',['link' => $link->getLink()]));
            return new RedirectResponse('/#login');
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            return $this->redirectToRoute('show_training_program', ['link'=> $link->getTrainingProgram()->getLink()]);
        } elseif ($user->hasRole('ROLE_CUSTOMER') || $user->hasRole('ROLE_VENDOR')) {
            return $this->render('FSTrainingProgramsBundle:Links:accessError.html.twig');
        }

        $accessManager = $this->get('fs_training.model.manager.access_manager');
        $access = $accessManager->getAccess($user, $link->getTrainingProgram());

        // redirect to training if user already has access to this training
        if ($access && $access->getTrainingState()) {
            return $this->redirectToRoute('show_training_program', ['link'=> $link->getTrainingProgram()->getLink()]);
        }

        return $this->render('FSTrainingProgramsBundle:TrainingPrograms/employee:show.html.twig', [
            'trainingProgram' => $link->getTrainingProgram(),
            'freeLink' => $link
        ]);
    }
}