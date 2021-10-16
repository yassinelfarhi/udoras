<?php

namespace FS\TrainingProgramsBundle\Model\Manager;


use FS\TrainingProgramsBundle\Entity\Repository\RequestRepository;
use FS\TrainingProgramsBundle\Entity\Request;
use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UdorasBundle\Util\Mailer;
use FS\UserBundle\Entity\Vendor;


class RequestManager
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

    public function getRequest(Vendor $vendor, TrainingProgram $trainingProgram)
    {
        $requestRepository = $this->em->getRepository('FSTrainingProgramsBundle:Request');

        return $requestRepository->findOneBy([
            'vendor' => $vendor,
            'trainingProgram' => $trainingProgram
        ]);
    }

    /**
     * @param Vendor $vendor
     * @param TrainingProgram $trainingProgram
     * @return Request|null
     */
    public function createRequest(Vendor $vendor, TrainingProgram $trainingProgram)
    {
        if (is_null($vendor) || is_null($trainingProgram)) {
            return null;
        }

        $request = new Request();
        $request->setTrainingProgram($trainingProgram);
        $request->setVendor($vendor);

        $this->em->persist($request);
        $this->update();

        return $request;
    }

    /**
     * @param $vendors
     * @param TrainingProgram $trainingProgram
     * @param Mailer $mailer
     */
    public function createRequests($vendors, TrainingProgram $trainingProgram, Mailer $mailer)
    {
        foreach ($vendors as $vendor) {
            if ($vendor instanceof Vendor) {
                if (is_null($this->getRequest($vendor, $trainingProgram))) {
                    $mailer->sendRequestMessage($vendor, $trainingProgram);
                    $this->createRequest($vendor, $trainingProgram);
                }
            }
        }

        $this->update();
    }
    
    private function update()
    {
        $this->em->flush();
    }
}