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
 * @ORM\Table(name="training_accesses")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\AccessRepository")
 */
class Access
{
    const NOT_PAID = 'not_paid';
    const PAID = 'paid';
    const FREE = 'free';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FS\TrainingProgramsBundle\Entity\TrainingProgram", inversedBy="accesses")
     * @ORM\JoinColumn(name="training_program_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $trainingProgram;

    /**
     * @ORM\ManyToOne(targetEntity="FS\UserBundle\Entity\Employee", inversedBy="accesses")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $employee;

    /**
     * @ORM\ManyToOne(targetEntity="FS\TrainingProgramsBundle\Entity\Request", inversedBy="accesses")
     * @ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $request;

    /**
     * @ORM\OneToOne(
     *     targetEntity="FS\TrainingProgramsBundle\Entity\EmployeeTrainingState",
     *     mappedBy="access",
     *     cascade={"all"}
     * )
     */
    protected $trainingState;

    /**
     * @ORM\Column(type="string")
     */
    protected $state = self::NOT_PAID;

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
     * @return Access
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
     * Set employee
     *
     * @param \FS\UserBundle\Entity\Employee $employee
     *
     * @return Access
     */
    public function setEmployee(\FS\UserBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \FS\UserBundle\Entity\Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Set request
     *
     * @param \FS\TrainingProgramsBundle\Entity\Request $request
     *
     * @return Access
     */
    public function setRequest(\FS\TrainingProgramsBundle\Entity\Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return \FS\TrainingProgramsBundle\Entity\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set trainingState
     *
     * @param \FS\TrainingProgramsBundle\Entity\EmployeeTrainingState $trainingState
     *
     * @return Access
     */
    public function setTrainingState(\FS\TrainingProgramsBundle\Entity\EmployeeTrainingState $trainingState = null)
    {
        $this->trainingState = $trainingState;

        return $this;
    }

    /**
     * Get trainingState
     *
     * @return \FS\TrainingProgramsBundle\Entity\EmployeeTrainingState
     */
    public function getTrainingState()
    {
        return $this->trainingState;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Access
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if (empty($this->state)) {
            return 'Not paid';
        }

        return ucfirst(str_replace('_', ' ', $this->state));
    }
}
