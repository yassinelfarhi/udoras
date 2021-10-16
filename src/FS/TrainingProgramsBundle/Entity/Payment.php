<?php

namespace FS\TrainingProgramsBundle\Entity;


use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\PaymentsRepository")
 */
class Payment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="FS\TrainingProgramsBundle\Entity\TrainingProgram")
     * @ORM\JoinColumn(name="training_program_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $trainingProgram;

    /**
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @ORM\Column(type="float")
     */
    protected $totalPrice;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Payment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Set current date
     *
     * @return Payment
     */
    public function setCurrentDate()
    {
        $this->date = new \DateTime();

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set totalPrice
     *
     * @param float $totalPrice
     *
     * @return Payment
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set user
     *
     * @param \FS\UserBundle\Entity\User $user
     *
     * @return Payment
     */
    public function setUser(\FS\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \FS\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set trainingProgram
     *
     * @param \FS\TrainingProgramsBundle\Entity\TrainingProgram $trainingProgram
     *
     * @return Payment
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
}
