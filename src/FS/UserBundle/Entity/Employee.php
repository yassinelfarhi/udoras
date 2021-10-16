<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 23.09.2016
 * Time: 18:54
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FS\TrainingProgramsBundle\Entity\Access;
use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Class Employee
 * @package FS\UserBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="user_employees")
 * @ORM\Entity(repositoryClass="FS\UserBundle\Entity\Repository\EmployeeRepository")
 *
 * @UniqueEntity(
 *     fields="emailCanonical",
 *     targetClass="FS\UserBundle\Entity\User",
 *     message="This email is already used",
 *     errorPath="email",
 * )
 * @UniqueEntity(
 *     fields="phone",
 *     targetClass="FS\UserBundle\Entity\User",
 *     message="This number is already used"
 * )
 */
class Employee extends User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Regex(
     *     pattern="/^[0-9]{4}/",
     *     message="Your last four of SNN data are invalid"
     * )
     * @Assert\NotBlank()
     */
    protected $lastFourOfSSN;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date()
     * @Assert\GreaterThan("-80 years UTC")
     * @Assert\LessThan("-16 years")
     * @Assert\NotBlank()
     */
    protected $birthday;

    /**
     * @ORM\ManyToOne(targetEntity="Vendor", inversedBy="employees")
     * @ORM\JoinColumn(name="v_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $vendor;

    /**
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\Access", mappedBy="employee")
     */
    private $accesses;

    public function __construct()
    {
        parent::__construct();
        $this->accesses = new ArrayCollection();
        $this->addRole("ROLE_EMPLOYEE");
    }

    public function hasAccessTo(User $user)
    {
        if ($user instanceof Employee) {
            return $user == $this;
        }

        return false;
    }

    /**
     * Set lastFourOfSSN
     *
     * @param string $lastFourOfSSN
     *
     * @return Employee
     */
    public function setLastFourOfSSN($lastFourOfSSN)
    {
        $this->lastFourOfSSN = $lastFourOfSSN;

        return $this;
    }

    /**
     * Get lastFourOfSSN
     *
     * @return string
     */
    public function getLastFourOfSSN()
    {
        return $this->lastFourOfSSN;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return Employee
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param mixed $vendor
     * @return $this
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     * Add access
     *
     * @param \FS\TrainingProgramsBundle\Entity\Access $access
     *
     * @return Employee
     */
    public function addAccess(\FS\TrainingProgramsBundle\Entity\Access $access)
    {
        $this->accesses[] = $access;

        return $this;
    }

    /**
     * Remove access
     *
     * @param \FS\TrainingProgramsBundle\Entity\Access $access
     */
    public function removeAccess(\FS\TrainingProgramsBundle\Entity\Access $access)
    {
        $this->accesses->removeElement($access);
    }

    /**
     * Get accesses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccesses()
    {
        return $this->accesses;
    }


    /**
     * @param TrainingProgram $trainingProgram
     * @return bool
     */
    public function hasTrainingAccess(TrainingProgram $trainingProgram)
    {
        foreach ($this->accesses as $access) {
            if ($access->getTrainingProgram() != null
                && $access->getTrainingProgram()->getId() == $trainingProgram->getId()) {
                return true;
            }
        }

        return false;
    }

//    /**
//     * @return bool
//     */
//    public function hasCertificates()
//    {
//        if($this->accesses->count() > 0){
//            /** @var Access $access */
//            foreach ($this->accesses as $access){
//                $trainingState = $access->getTrainingState();
//
//                if (
//                    $trainingState && $trainingState->getPassedStatus() === EmployeeTrainingState::FINAL_STATUS_PASSED
//                ) {
//                    return true;
//                }
//            }
//        }
//
//        return false;
//    }

    /**
     * @return bool
     */
    public function hasCertificates()
    {
        if($this->accesses->count() > 0){
            /** @var Access $access */
            foreach ($this->accesses as $access){
                $trainingState = $access->getTrainingState();

                if (
                    $trainingState &&
                    ($trainingState->getPassedStatus() === EmployeeTrainingState::FINAL_STATUS_PASSED ||
                    $trainingState->getPassedStatus() === EmployeeTrainingState::FINAL_STATUS_FAILED)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
