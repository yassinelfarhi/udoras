<?php

namespace FS\TrainingProgramsBundle\Model\Manager;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\Request;
use FS\TrainingProgramsBundle\Entity\Slide;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UdorasBundle\Util\Mailer;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\Vendor;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AccessManager
{
    protected $em;

    /**
     * RequestManager constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Employee $employee
     * @param TrainingProgram $trainingProgram
     * @param Request $request
     * @return Access|null
     */
    public function createAccess(Employee $employee, TrainingProgram $trainingProgram, Request $request = null)
    {
        if (is_null($employee) || is_null($trainingProgram)) {
            return null;
        }

        $access = $this->getAccess($employee, $trainingProgram);

        if ($access !== null) {
            return $access;
        }

        $access = new Access();
        $access->setTrainingProgram($trainingProgram);
        $access->setEmployee($employee);

        if ($request) {
            $access->setRequest($request);
        }

        if ($trainingProgram->getPrice() == 0) {
            $access->setState(Access::PAID);
            $this->addNewTrainingStateToAccess($access);
        }

        $this->em->persist($access);
        $this->update();

        return $access;
    }

    /**
     * @param Employee $employee
     * @param TrainingProgram $trainingProgram
     * @param Request|null $request
     * @return Access|null
     */
    public function createAccessIfNotExists(Employee $employee, TrainingProgram $trainingProgram, Request $request = null)
    {
        if (is_null($employee) || is_null($trainingProgram)) {
            return null;
        }

        $access = $this->getAccess($employee, $trainingProgram);

        if ($access !== null) {
            return $access;
        }

        $access = new Access();
        $access->setTrainingProgram($trainingProgram);
        $access->setEmployee($employee);

        if ($request) {
            $access->setRequest($request);
        }

        if ($trainingProgram->getPrice() == 0) {
            $access->setState(Access::PAID);
            $this->addNewTrainingStateToAccess($access);
        }

        $this->em->persist($access);
        $this->update();

        return $access;
    }

    /**
     * @param $employees
     * @param TrainingProgram $trainingProgram
     * @param Request|null $request
     * @param Mailer $mailer
     */
    public function createAccesses(
        $employees,
        TrainingProgram $trainingProgram,
        Request $request = null,
        Mailer $mailer
    )
    {
        foreach ($employees as $employee) {
            if ($employee instanceof Employee) {
                if (is_null($this->getAccess($employee, $trainingProgram))) {
                    $mailer->sendRequestMessage($employee, $trainingProgram);
                    $this->createAccess($employee, $trainingProgram, $request);
                }
            }
        }

        $this->update();
    }

    /**
     * @param $employees
     * @param TrainingProgram $trainingProgram
     * @param Request|null $request
     * @param Mailer $mailer
     */
    public function updateAccessesForVendor(Vendor $vendor, Request $request)
    {
        $trainingProgram = $request->getTrainingProgram();
        $employees = $vendor->getEmployees();
        /** @var Employee $employee */
        foreach ($employees as $employee) {
            $access = $this->getAccess($employee, $trainingProgram);
            if (!empty($access) && $access->getState() === Access::NOT_PAID && empty($access->getRequest())) {
                $access->setRequest($request);
                $this->em->persist($access);
            }
            $this->em->persist($employee);
        }

        $this->update();
    }

    /**
     * @param Employee $employee
     * @param TrainingProgram $trainingProgram
     * @return Access
     */
    public function getAccess(Employee $employee, TrainingProgram $trainingProgram)
    {
        $accessRepository = $this->em->getRepository('FSTrainingProgramsBundle:Access');

        return $accessRepository->findOneBy([
            'employee' => $employee,
            'trainingProgram' => $trainingProgram
        ]);
    }

    /**
     * @param Access $access
     * @param $state
     * @return Access|null
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function addNewTrainingStateToAccessAndUpdateState(Access $access, $state)
    {
        if (is_null($access) || $access->getTrainingState() !== null) {
            return $access;
        }

        $trainingState = new EmployeeTrainingState();
        $em = $this->em;

        $trainingProgram = $access->getTrainingProgram();

        /** @var Slide $firstSlide */
        $firstSlide = $em->getRepository('FSTrainingProgramsBundle:Slide')
            ->getSlideByTrainingProgramAndRealNum($trainingProgram);

        if ($firstSlide) {
            $trainingState->setNextSlide($firstSlide->getId());
        }

        $trainingState->setAccess($access);
        $trainingState->setTraining($trainingProgram);

        $access->setTrainingState($trainingState);
        $access->setState($state);

        $this->em->persist($trainingState);
        $this->update();

        return $access;
    }

    /**
     * @param Access $access
     * @return Access|EmployeeTrainingState
     */
    public function addNewTrainingStateToAccess(Access &$access)
    {
        if (is_null($access) || $access->getTrainingState() !== null) {
            return $access;
        }

        $trainingProgram = $access->getTrainingProgram();
        $firstSlide = $this->em->getRepository('FSTrainingProgramsBundle:Slide')
            ->getSlideByTrainingProgramAndRealNum($trainingProgram);
        $trainingState = new EmployeeTrainingState();

        if ($firstSlide) {
            $trainingState->setNextSlide($firstSlide->getId());
        }

        $trainingState->setAccess($access);
        $trainingState->setTraining($trainingProgram);
        $this->em->persist($trainingState);
        $access->setTrainingState($trainingState);

        return $trainingState;
    }

    /**
     * @param Access $access
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function bookTrainingBoughtByVendor(Access &$access)
    {
        $request = $access->getRequest();

        if (is_null($access)) {
            throw new NotFoundHttpException('Not found Access for given employee and training program not found');
        }

        if ($access->getTrainingState() || is_null($request) || $request->getAmountOfTrainings() < 1) {
            throw new AccessDeniedException('Access denied. This Employee can not add the training program');
        }

        $this->em->getConnection()->beginTransaction();

        $this->addNewTrainingStateToAccessAndUpdateState($access, Access::PAID);
        $request->useTraining();
        $this->update();

        $this->em->getConnection()->commit();
    }

    /**
     * update db
     */
    private function update()
    {
        $this->em->flush();
    }
}