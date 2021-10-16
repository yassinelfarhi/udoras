<?php

namespace FS\UdorasBundle\Controller;

use FS\UdorasBundle\Annotation\ResourceManipulation;
use FS\UdorasBundle\Entity\DeleteQueue;
use FS\UdorasBundle\Entity\Lock;
use FS\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class InternalController
 * @package FS\UdorasBundle\Controller
 * @author <vladislav@fora-soft.com>
 */
class InternalController extends Controller
{
    /**
     * @param Request $request
     * @param $resource
     * @param $id
     * @return JsonResponse
     * @Route("/lock/{resource}/{id}/{path}", name="try_lock_resource")
     * @Security(expression="is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')")
     * @ResourceManipulation
     */
    public function tryLockResourceAction(Request $request, $resource, $id, $path)
    {
        $em = $this->getDoctrine()
            ->getManager();


        $exist = false;
        if (strtolower($resource) == 'trainingprogram') {
            $exist = $em->find(("FSTrainingProgramsBundle:" . ucfirst($resource)), $id);
        } else {
            $exist = $em->find(("FSUserBundle:" . $resource), $id);
        }
        if (!$exist) {
            return new JsonResponse([
                    'error',
                    'modal' => $this->renderView("FSUdorasBundle:Internal:deleted.html.twig", [
                        'resource' => $resource
                    ]),
                ]
            );
        }
        /** @var User $user */
        $user = $this->getUser();
        $ssid = $request->getSession()->getId();

        /** @var Lock $lock */
        $lock = $em->getRepository('FSUdorasBundle:Lock')->findOneBy(
            [
                'resource' => $resource,
                'resourceId' => $id
            ]
        );
        if ($lock) {
            if ($lock->getSessionId() == $ssid && $user->getId() == $lock->getId()) {
                /** NOP */
            } else if ($lock->getStatus() == Lock::LOCK__STATUS_LOCKED) {
                return new JsonResponse([
                        'error',
                        'modal' => $this->renderView("FSUdorasBundle:Internal:tryLockResource.html.twig", [
                            'resource' => $resource
                        ]),
                    ]
                );
            } else {
                $lock->setSessionId($ssid);
                $lock->setUserId($user->getId());
                $lock->setStatus(Lock::LOCK__STATUS_LOCKED);
                $em->persist($lock);
                $em->flush();
            }
        } else {
            //Creating new lock
            $lock = new Lock();
            $lock->setSessionId($ssid);
            $lock->setUserId($user->getId());
            $lock->setStatus(Lock::LOCK__STATUS_LOCKED);
            $lock->setResource($resource);
            $lock->setResourceId($id);
            $em->persist($lock);
            $em->flush();
        }

        return new JsonResponse([
                'processed',
                'target' => $this->generateUrl(
                    $path,
                    [
                        $resource => $id
                    ]
                )
            ]
        );
    }

    /**
     * @param Request $request
     * @param $resource
     * @param $id
     * @return JsonResponse
     * @Route("/delete/{resource}/{id}", name="try_delete_resource")
     * @Security(expression="is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')")
     * @ResourceManipulation
     * TODO Has access to resource
     */
    public function tryDeleteResourceAction(Request $request, $resource, $id)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $exist = false;
        if (strtolower($resource) == 'trainingprogram') {
            $exist = $em->find(("FSTrainingProgramsBundle:" . ucfirst($resource)), $id);
        } else {
            $exist = $em->find(("FSUserBundle:" . $resource), $id);
        }

        if (!$exist) {
            return new JsonResponse([
                    'error',
                    'modal' => $this->renderView("FSUdorasBundle:Internal:deleted.html.twig", [
                        'resource' => $resource
                    ]),
                ]
            );
        }

        /** @var User $user */
        $user = $this->getUser();
        $ssid = $request->getSession()->getId();

        /** @var Lock $lock */
        $lock = $em->getRepository('FSUdorasBundle:Lock')->findOneBy(
            [
                'resource' => $resource,
                'resourceId' => $id
            ]
        );

        if ($lock) {
            if ($lock->getSessionId() == $ssid && $user->getId() == $lock->getId()) {
                /** NOP */
            } else if ($lock->getStatus() == Lock::LOCK__STATUS_LOCKED) {
                return new JsonResponse([
                        'error',
                        'modal' => $this->renderView("FSUdorasBundle:Internal:tryLockResource.html.twig",
                        [
                            'resource' => $resource,
                        ]
                        ),
                    ]
                );
            } else {
                $lock->setSessionId($ssid);
                $lock->setUserId($user->getId());
                $lock->setStatus(Lock::LOCK__STATUS_LOCKED);
                $em->persist($lock);
                $em->flush();
            }
        } else {
            //Creating new lock
            $lock = new Lock();
            $lock->setSessionId($ssid);
            $lock->setUserId($user->getId());
            $lock->setStatus(Lock::LOCK__STATUS_LOCKED);
            $lock->setResource($resource);
            $lock->setResourceId($id);
            $em->persist($lock);
            $em->flush();
        }

        $deleteQueue = new DeleteQueue();
        $deleteQueue->setResourceId($id);
        $deleteQueue->setLockId($lock->getId());
        $deleteQueue->setResource($resource);
        $deleteQueue->setSessionId($ssid);
        $deleteQueue->setUserId($user->getId());

        $em->persist($deleteQueue);
        $em->flush();

        return new JsonResponse([
                'processed',
                'target' => [
                    'name' => 'Restore',
                    'href' => $this->generateUrl('restore_deleted_resource', [
                        'resource' => $resource,
                        'id' => $id
                    ], UrlGenerator::ABSOLUTE_PATH),
                    'extra' => true,
                    'addClass' => 'btn-grey',
                    'removeClass' => 'btn-red'
                ],
                'resource' => [
                    'id' => $id,
                    'resource' => $resource,
                ],
            ]
        );
    }

    /**
     * @param Request $request
     * @param $resource
     * @param $id
     * @return JsonResponse
     * @Route("/restore/{resource}/{id}", name="restore_deleted_resource")
     * @Security(expression="is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY')")
     * @ResourceManipulation
     * TODO Has access to resource
     */
    public function restoreDeletedResourceAction(Request $request, $resource, $id)
    {

        $em = $this->getDoctrine()
            ->getManager();

        /** @var User $user */
        $user = $this->getUser();
        $ssid = $request->getSession()->getId();

        $queue = $em->getRepository('FSUdorasBundle:DeleteQueue')->findOneBy(
            [
                'resourceId' => $id,
                'resource' => $resource,
                'userId' => $user->getId(),
                'sessionId' => $ssid,
            ]
        );
        if ($queue) {
            $lock = $em->find("FSUdorasBundle:Lock", $queue->getLockId());
            $lock->setStatus(Lock::LOCK__STATUS_FREE);
            $em->persist($lock);
            $em->remove($queue);
            $em->flush();
            return new JsonResponse([
                    'processed',
                    'target' => [
                        'name' => 'Delete',
                        'href' => $this->generateUrl('try_delete_resource', [
                            'resource' => $resource,
                            'id' => $id
                        ], UrlGenerator::ABSOLUTE_PATH),
                        'extra' => false,
                        'addClass' => 'btn-red',
                        'removeClass' => 'btn-grey'
                    ],
                    'resource' => [
                        'id' => $id,
                        'resource' => $resource
                    ],
                ]
            );
        } else {
            return new JsonResponse(['error', 'delete queue not found']);
        }
    }


    /**
     * @param Request $request
     * @param $user
     * @param $ssid
     * @return JsonResponse
     * @Security(expression="!(is_granted('IS_AUTHENTICATED_REMEMBERED') || is_granted('IS_AUTHENTICATED_FULLY'))")
     * @Route("/release/{user}/{ssid}", name="release_locked_resource")
     * @ResourceManipulation
     */
    public function releaseResourceAction(Request $request, $user, $ssid)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $locks = $em->getRepository('FSUdorasBundle:Lock')->findBy(
            [
                'userId' => $user,
                'sessionId' => $ssid,
            ]
        );

        /** @var Lock $lock */
        foreach ($locks as $lock) {
            if ($lock->getStatus() == Lock::LOCK__STATUS_LOCKED) {
                $lock->setStatus(Lock::LOCK__STATUS_FREE);
                $em->persist($lock);
                $em->flush();
            }
        }
        return new JsonResponse(['processed']);
    }

    /**
     * @param Request $request
     * @param $user
     * @param $ssid
     * @return JsonResponse
     * @Route("/flush/{user}/{ssid}", name="flush_deleted_resource")
     * @ResourceManipulation
     */
    public function flushDeleteQueueAction(Request $request, $user, $ssid)
    {

        $em = $this->getDoctrine()
            ->getManager();

        $queue = $em->getRepository('FSUdorasBundle:DeleteQueue')->findBy(
            [
                'userId' => $user,
                'sessionId' => $ssid,
                'status' => DeleteQueue::DELETE_QUEUE__STATUS_WAIT
            ]
        );

        /** @var DeleteQueue $q */
        foreach ($queue as $q) {
            if (strtolower($q->getResource()) == 'trainingprogram') {
                $tp = $em->find(("FSTrainingProgramsBundle:" . ucfirst($q->getResource())), $q->getResourceId());
                $em->remove($tp);
            } else {
                $discriminator = $this->get('pugx_user.manager.user_discriminator');
                $discriminator->setClass('FS\UserBundle\Entity' . '\\' . ucfirst($q->getResource()));
                $userManager = $this->get('pugx_user_manager');
                $user = $userManager->findUserBy(['id' => $q->getResourceId()]);

                if ($user) {
                    $userManager->deleteUser($user);
                }
            }

            $q->setStatus(DeleteQueue::DELETE_QUEUE__STATUS_COMPLETE);
            $em->persist($q);
            $lock = $em->find("FSUdorasBundle:Lock", $q->getLockId());
            $em->remove($lock);
        }
        $em->flush();

        return new JsonResponse(['processed']);
    }
}
