<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 23.09.2016
 * Time: 19:02
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FS\UserBundle\Model\Address\AddressInterface;
use FS\UserBundle\Model\Address\AddressTrait;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;

use FS\TrainingProgramsBundle\Validator\Constraints as FSAssert;

/**
 * Class Customer
 * @package FS\UserBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="user_customers")
 * @ORM\Entity(repositoryClass="FS\UserBundle\Entity\Repository\CustomerRepository")
 *
 * @UniqueEntity(
 *     fields="emailCanonical",
 *     targetClass="FS\UserBundle\Entity\User",
 *     message="This email is already used",
 *     errorPath="email",
 * )
 *
 * @UniqueEntity(
 *     fields="phone",
 *     targetClass="FS\UserBundle\Entity\User",
 *     message="This number is already used"
 * )
 */
class Customer extends User implements AddressInterface
{

    use AddressTrait;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $company;

    /**
     * @ORM\OneToMany(targetEntity="Vendor", mappedBy="customer")
     */
    private $vendors;

    /**
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\TrainingProgram", mappedBy="customer", cascade={"persist"})
     */
    private $trainingPrograms;

    public function __construct()
    {
        parent::__construct();
        $this->vendors = new ArrayCollection();
        $this->trainingPrograms = new ArrayCollection();
        $this->addRole("ROLE_CUSTOMER");
    }

    /**
     * Check access to other users
     *
     * @param User $user
     * @return bool
     */
    public function hasAccessTo(User $user)
    {
        if ($user instanceof Customer) {
            return $user == $this;
        } elseif ($user instanceof Vendor) {
            return $user->getCustomer() == $this;
        } elseif ($user instanceof Employee) {
            return $user->getVendor()->getCustomer() == $this;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Add vendor
     *
     * @param \FS\UserBundle\Entity\Vendor $vendor
     *
     * @return Customer
     */
    public function addVendor(\FS\UserBundle\Entity\Vendor $vendor)
    {
        $this->vendors[] = $vendor;

        return $this;
    }

    /**
     * Remove vendor
     *
     * @param \FS\UserBundle\Entity\Vendor $vendor
     */
    public function removeVendor(\FS\UserBundle\Entity\Vendor $vendor)
    {
        $this->vendors->removeElement($vendor);
    }

    /**
     * Get vendors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVendors()
    {
        return $this->vendors;
    }

    /**
     * Add trainingProgram
     *
     * @param TrainingProgram $trainingProgram
     *
     * @return Customer
     */
    public function addTrainingProgram(TrainingProgram $trainingProgram)
    {
        $this->trainingPrograms[] = $trainingProgram;
        $trainingProgram->setCustomer($this);

        return $this;
    }

    /**
     * Remove trainingProgram
     *
     * @param TrainingProgram $trainingProgram
     */
    public function removeTrainingProgram(TrainingProgram $trainingProgram)
    {
        $this->trainingPrograms->removeElement($trainingProgram);
    }

    /**
     * Get trainingPrograms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrainingPrograms()
    {
        return $this->trainingPrograms;
    }
}
