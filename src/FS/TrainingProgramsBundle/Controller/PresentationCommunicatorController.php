<?php

namespace FS\TrainingProgramsBundle\Controller;

use Doctrine\Common\Collections\Collection;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UdorasBundle\Annotation\ResourceManipulation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PresentationCommunicatorController
 * @package FS\TrainingProgramsBundle\Controller
 * @author <vladislav@fora-soft.com>
 * @Route(path="/presentation")
 */
class PresentationCommunicatorController extends Controller
{
    /**
     * @param Request $request
     * @param TrainingProgram $presentation
     * @return array
     * @Route(path="/communicator/{presentation}", name="presentation_communicator", methods={"POST"})
     * @ResourceManipulation
     */
    public function communicateAction(Request $request, TrainingProgram $presentation)
    {
        $cmd = $request->request->get('command', 'nothing');
        $res = $this->get('fs.training_program.factory')
            ->factory(
                $cmd,
                $presentation
            )
            ->execute();

        if ($res instanceof Slide) {
            return new JsonResponse([
                'status' => 'processed',
                'command' => $cmd,
                'data' => $res->getId()
            ]);
        } elseif (is_array($res) || $res instanceof Collection) {
            if ($res instanceof Collection) {
                $res = $this->normalizeArray($res->toArray());
            }
            $serializer = $this->get('serializer');
            $res = $serializer->serialize(
                $res,
                'json',
                [
                    'groups' => ['presentationCreate']
                ]
            );
            return new JsonResponse([
                'status' => 'processed',
                'command' => $cmd,
                'data' => json_decode($res, true)
            ]);
        }

        return new JsonResponse([
            'status' => 'processed',
            'command' => $cmd,
        ]);
    }

    /**
     * @param Request $request
     * @param Slide $slide
     * @return array
     * @Route(path="/communicator/slide/{slide}", name="slide_communicator", methods={"POST"})
     * @ResourceManipulation
     */
    public function communicateSlideAction(Request $request, Slide $slide)
    {
        $cmd = $request->request->get('command', 'nothing');

        $res = $this->get('fs.slide.factory')
            ->factory(
                $cmd,
                $slide
            )
            ->execute();

        $serializer = $this->get('serializer');
        $res = $serializer->serialize(
            $res,
            'json',
            [
                'groups' => ['presentationCreate']
            ]
        );
        return new JsonResponse([
            'status' => 'processed',
            'command' => $cmd,
            'data' => json_decode($res, true)
        ]);
    }

    private function normalizeArray($data)
    {
        $res = [];
        foreach ($data as $item) {
            $res [] = $item;
        }
        return $res;
    }
}
