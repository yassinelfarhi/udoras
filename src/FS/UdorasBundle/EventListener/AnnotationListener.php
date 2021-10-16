<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 29.09.2016
 * Time: 11:51
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\EventListener;

use Doctrine\Common\Annotations\Reader;//This thing read annotations
use Doctrine\ORM\EntityManager;
use FS\UdorasBundle\Annotation\Resource;
use FS\UdorasBundle\Annotation\ResourceManipulation;
use FS\UdorasBundle\Entity\DeleteQueue;
use FS\UdorasBundle\Entity\Lock;
use FS\UserBundle\Entity\User;
use Predis\Client;
use PUGX\MultiUserBundle\Doctrine\UserManager;
use PUGX\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AnnotationListener
{
    protected $reader;
    private $requestStack;
    private $request;
    private $tokenStorage;
    private $entityManager;
    private $redisClient;
    private $router;
    private $discriminator;
    private $userManager;

    /**
     * @param Reader $reader
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @param EntityManager $entityManager
     * @param Client $redisClient
     * @param Router $router
     * @param UserDiscriminator $discriminator
     * @param UserManager $userManager
     */
    public function __construct(
        Reader $reader,
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        EntityManager $entityManager,
        Client $redisClient,
        Router $router,
        UserDiscriminator $discriminator,
        UserManager $userManager
    )
    {
        $this->reader = $reader;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getCurrentRequest();
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->redisClient = $redisClient;
        $this->router = $router;
        $this->discriminator = $discriminator;
        $this->userManager = $userManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        list($controllerObject, $methodName) = $controller;

       // Get method annotation
        $controllerReflectionObject = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);


        $releaseAll = true;
        foreach ($this->reader->getMethodAnnotations($reflectionMethod)  as $configuration) { //Start of annotations reading
            if ($configuration instanceof Resource) {
                /** @var User $user */
                $user = $this->getUser();
                $resource = $configuration->getResource();
                $resourceId = $this->request->attributes->get($resource, 0);
                $releaseAll = false;
                if (!$this->checkLock($resource, $resourceId)) {
                    $target = $this->router->generate('fs_basic_homepage');
                    if ($user->hasRole('ROLE_ADMIN')) {
                        $target = $this->router->generate('index_admin');
                    } elseif ($user->hasRole('ROLE_CUSTOMER')) {
                        $target = $this->router->generate('index_customer');
                    } elseif ($user->hasRole('ROLE_VENDOR')) {
                        $target = $this->router->generate('index_vendor');
                    } elseif ($user->hasRole('ROLE_EMPLOYEE')) {
                        $target = $this->router->generate('index_employee');
                    }

                    $referrer = $this->request->headers->get('referrer');
                    $event->setController(
                        function () use ($referrer, $target) {
                            return new RedirectResponse(
                                $referrer ? $referrer : $target
                            );
                        }
                    );
                }
            }
            if($configuration instanceof ResourceManipulation){
                $releaseAll = false;
            }
        }
        $user = $this->getUser();
        if($releaseAll && $user){
            $ssid = $this->request->getSession()->getId();
            //delete
            $queue = $this->entityManager->getRepository('FSUdorasBundle:DeleteQueue')->findBy(
                [
                    'userId' => $user,
                    'sessionId' => $ssid,
                    'status' => DeleteQueue::DELETE_QUEUE__STATUS_WAIT
                ]
            );

            foreach ($queue as $q) {
                if(strtolower($q->getResource()) == 'trainingprogram'){
                    $tp = $this->entityManager->find(
                        ("FSTrainingProgramsBundle:".ucfirst($q->getResource())),
                        $q->getResourceId()
                    );

                    if ($tp !== null) {
                        $this->entityManager->remove($tp);
                    }
                }else {
                    $this->discriminator->setClass('FS\UserBundle\Entity' . '\\' . ucfirst($q->getResource()));
                    $user = $this->userManager->findUserBy(['id' => $q->getResourceId()]);

                    if ($user !== null) {
                        $this->userManager->deleteUser($user);
                    }
                }

                $q->setStatus(DeleteQueue::DELETE_QUEUE__STATUS_COMPLETE);
                $this->entityManager->persist($q);
                $lock = $this->entityManager->find("FSUdorasBundle:Lock", $q->getLockId());

                if ($lock !== null) {
                    $this->entityManager->remove($lock);
                }
            }

            $this->entityManager->flush();
            //locks
            $locks = $this->entityManager->getRepository('FSUdorasBundle:Lock')->findBy(
                [
                    'userId' => $user,
                    'sessionId' => $ssid,
                ]
            );
            foreach ($locks as $lock) {
                if ($lock->getStatus() === Lock::LOCK__STATUS_LOCKED) {
                    $lock->setStatus(Lock::LOCK__STATUS_FREE);
                    $this->entityManager->persist($lock);
                    $this->entityManager->flush($lock);
                }
            }


        }
    }

    private function checkLock($resource, $id)
    {
        /** @var User $user */
        $user = $this->getUser();
        $ssid = $this->request->getSession()->getId();

        $lock = $this->entityManager->getRepository('FSUdorasBundle:Lock')->findOneBy(
            [
                'resource' => $resource,
                'resourceId' => $id
            ]
        );
        if($lock){
            if($lock->getStatus() == Lock::LOCK__STATUS_FREE){
                $lock->setSessionId($ssid);
                $lock->setUserId($user->getId());
                $lock->setStatus(Lock::LOCK__STATUS_LOCKED);
                $this->entityManager->persist($lock);
                $this->entityManager->flush();
                return true;
            } else {
                if ($lock->getSessionId() == $ssid && $lock->getUserId() == $user->getId()) {
                    return true;
                }
            }
        } else {
            $lock = new Lock();
            $lock->setSessionId($ssid);
            $lock->setUserId($user->getId());
            $lock->setStatus(Lock::LOCK__STATUS_LOCKED);
            $lock->setResource($resource);
            $lock->setResourceId($id);
            $this->entityManager->persist($lock);
            $this->entityManager->flush();

            return true;
        }
        
        return false;
    }

    private function getUser()
    {
        if (!$this->tokenStorage) {
            return null;
        }
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }
        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}