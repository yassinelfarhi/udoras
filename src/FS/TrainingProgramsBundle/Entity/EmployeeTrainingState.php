<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 21.10.2016
 * Time: 18:30
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class EmployeeTrainingState
 * @package FS\TrainingProgramsBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="employee_training_state")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\EmployeeTrainingStateRepository")
 */
class EmployeeTrainingState
{

    const  TIME_STATUS__NO_TIME = 'no_time';
    const  TIME_STATUS__TIMER = 'timer';

    const FINAL_STATUS_PASSED = 'passed';
    const FINAL_STATUS_FAILED = 'failed';
    const FINAL_STATUS_IN_PROGRESS = 'in_progress';


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;


    /**
     * @var Access
     *
     * @ORM\OneToOne(targetEntity="FS\TrainingProgramsBundle\Entity\Access", inversedBy="trainingState")
     * @ORM\JoinColumn(name="training_access_id", referencedColumnName="id")
     */
    protected $access;


    /**
     * @var TrainingProgram
     * @ORM\ManyToOne(targetEntity="FS\TrainingProgramsBundle\Entity\TrainingProgram")
     * @ORM\JoinColumn(name="tp_id", referencedColumnName="id")
     */
    protected $training;

    /**
     * @var integer
     *  slide id
     *
     * @ORM\Column(type="integer")
     */
    protected $currentSlide = 0;


    /**
     * @var integer
     *
     * next slide id
     *
     * @ORM\Column(type="integer")
     */
    protected $nextSlide = 0;


    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $timeStatus = self::TIME_STATUS__NO_TIME;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $timeRemaining = 0;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startTimer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endTimer;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $paused = false;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pauseStart;


    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"default":0})
     */
    protected $remainTimePause = 0;


    /**
     * Status of presentation
     */

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $passedStatus = self::FINAL_STATUS_IN_PROGRESS;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true);
     */
    protected $endTime;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $timeOffset = 0;

    /**
     * @var float
     * @ORM\Column(type="float")
     */
    protected $ratio = 0.0;

    /**
     * @var string
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    protected $certificateId = null;
    /**
     * @var \DateTime
     * @ORM\Column(name="valid_until", nullable=true, type="datetime")
     */
    protected $validUntil = null;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TrainingProgram
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * @param TrainingProgram $training
     */
    public function setTraining($training)
    {
        $this->training = $training;
    }

    /**
     * @return integer
     */
    public function getCurrentSlide()
    {
        return $this->currentSlide;
    }

    /**
     * @param integer $currentSlide
     */
    public function setCurrentSlide($currentSlide)
    {
        $this->currentSlide = $currentSlide;
    }

    /**
     * @return integer
     */
    public function getNextSlide()
    {
        return $this->nextSlide;
    }

    /**
     * @param integer $nextSlide
     */
    public function setNextSlide($nextSlide)
    {
        $this->nextSlide = $nextSlide;
    }

    /**
     * @return string
     */
    public function getTimeStatus()
    {
        return $this->timeStatus;
    }

    /**
     * @param string $timeStatus
     */
    public function setTimeStatus($timeStatus)
    {
        $this->timeStatus = $timeStatus;
    }

    /**
     * @return int
     */
    public function getTimeRemaining()
    {
        return $this->timeRemaining;
    }

    /**
     * @param int $timeRemaining
     */
    public function setTimeRemaining($timeRemaining)
    {
        $this->timeRemaining = $timeRemaining;
    }

    /**
     * @return \DateTime
     */
    public function getStartTimer()
    {
        return $this->startTimer;
    }

    /**
     * @param \DateTime $startTimer
     */
    public function setStartTimer($startTimer)
    {
        $this->startTimer = $startTimer;
    }

    /**
     * @return \DateTime
     */
    public function getEndTimer()
    {
        return $this->endTimer;
    }

    /**
     * @param \DateTime $endTimer
     */
    public function setEndTimer($endTimer)
    {
        $this->endTimer = $endTimer;
    }

    /**
     * Get paused
     *
     * @return boolean
     */
    public function getPaused()
    {
        return $this->paused;
    }

    /**
     * @param boolean $paused
     */
    public function setPaused($paused)
    {
        $this->paused = $paused;
    }

    /**
     * Get access
     *
     * @return Access
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set access
     *
     * @param Access $access
     *
     * @return EmployeeTrainingState
     */
    public function setAccess(Access $access = null)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassedStatus()
    {
        return $this->passedStatus;
    }

    /**
     * @param string $passedStatus
     */
    public function setPassedStatus($passedStatus)
    {
        $this->passedStatus = $passedStatus;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return int
     */
    public function getTimeOffset()
    {
        return $this->timeOffset;
    }

    /**
     * @param int $timeOffset
     */
    public function setTimeOffset($timeOffset)
    {
        $this->timeOffset = $timeOffset;
    }

    /**
     * @return float
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param float $ratio
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    }

    public function isPassed()
    {
        return $this->passedStatus === self::FINAL_STATUS_PASSED;
    }

    /**
     * @return \DateTime
     */
    public function getPauseStart()
    {
        return $this->pauseStart;
    }

    /**
     * @param \DateTime $pauseStart
     */
    public function setPauseStart($pauseStart)
    {
        $this->pauseStart = $pauseStart;
    }

    /**
     * @return int
     */
    public function getRemainTimePause()
    {
        return $this->remainTimePause;
    }

    /**
     * @param int $remainTimePause
     */
    public function setRemainTimePause($remainTimePause)
    {
        $this->remainTimePause = $remainTimePause;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getTraining()->getTitle();
    }

    /**
     * @return string
     */
    public function getCertificateId()
    {
        return $this->certificateId;
    }

    /**
     * @param string $certificateId
     * @return EmployeeTrainingState
     */
    public function setCertificateId($certificateId)
    {
        $this->certificateId = $certificateId;
        return $this;
    }

    /**
     * @return EmployeeTrainingState
     */
    public function generateCertificateId()
    {
        $this->certificateId = substr(md5(uniqid("certificate")), 0, 8);
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @param \DateTime $validUntil
     * @return EmployeeTrainingState
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    public function fillValidUntil()
    {
        $months = $this->getTraining()->getCertificateValidMonths();
        $validUntil = $this->getTraining()->getCertificateValidUntil();
        if ($months > 0) {
            $validUntil = clone $this->getEndTime();
            $this->validUntil = $validUntil->modify("+$months month");
        } else if (!empty($validUntil)) {
            $this->validUntil = $validUntil;
        } else {
            $validUntil = clone $this->getEndTime();
            $this->validUntil = $validUntil->modify("+2 year");
        }
    }
}
