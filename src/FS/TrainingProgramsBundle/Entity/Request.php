<?php

namespace FS\TrainingProgramsBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FS\UserBundle\Entity\Employee;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use FS\UserBundle\Entity\Customer;
use FS\TrainingProgramsBundle\Entity\Slide;


/**
 * Class TrainingRequest
 * @package FS\TrainingProgramsBundle\Entity
 *
 * @ORM\Table(name="training_requests")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\RequestRepository")
 */
class Request
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FS\TrainingProgramsBundle\Entity\TrainingProgram", inversedBy="requests")
     * @ORM\JoinColumn(name="training_program_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $trainingProgram;

    /**
     * @ORM\ManyToOne(targetEntity="FS\UserBundle\Entity\Vendor", inversedBy="requests")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $vendor;

    /**
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\Access", mappedBy="request")
     */
    protected $accesses;

    /**
     * @ORM\Column(type="integer",)
     */
    protected $amountOfTrainings = 0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set trainingProgram
     *
     * @param \FS\TrainingProgramsBundle\Entity\TrainingProgram $trainingProgram
     *
     * @return Request
     */
    public function setTrainingProgram(\FS\TrainingProgramsBundle\Entity\TrainingProgram $trainingProgram = null)
    {
        $this->trainingProgram = $trainingProgram;

        return $this;
    }

    /**
     * Get trainingProgram
     *
     * @return \FS\TrainingProgramsBundle\Entity\TrainingProgram
     */
    public function getTrainingProgram()
    {
        return $this->trainingProgram;
    }

    /**
     * Set vendor
     *
     * @param \FS\UserBundle\Entity\Vendor $vendor
     *
     * @return Request
     */
    public function setVendor(\FS\UserBundle\Entity\Vendor $vendor = null)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get vendor
     *
     * @return \FS\UserBundle\Entity\Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Get amount of trainings bought by vendor
     *
     * @return int
     */
    public function getAmountOfTrainings()
    {
        return $this->amountOfTrainings;
    }

    /**
     * Add to amount of trainings when vendor buys trainings
     *
     * @param $amount
     * @return bool
     */
    public function addToAmountOfTrainings($amount)
    {
        if ($amount > 0) {
            $this->amountOfTrainings += $amount;

            return true;
        }

        return false;
    }

    /**
     * Use training when employee add training
     *
     * @return bool
     */
    public function useTraining()
    {
        if ($this->amountOfTrainings > 0) {
            $this->amountOfTrainings -= 1;

            return true;
        }

        return false;
    }

    /**
     * get Status of vendor training request
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->getTrainingProgram()->getPrice() == 0) {
          return 'Paid';
        } elseif ($this->amountOfTrainings == 0) {
            return 'Not Paid';
        } elseif ($this->amountOfTrainings == 1) {
            return 'Paid for 1 Training';
        }

        return 'Paid for ' . $this->amountOfTrainings . ' Trainings';
    }
}
