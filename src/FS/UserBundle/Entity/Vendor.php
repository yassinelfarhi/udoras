<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 23.09.2016
 * Time: 18:59
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UserBundle\Model\Address\AddressInterface;
use FS\UserBundle\Model\Address\AddressTrait;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use FS\UserBundle\Entity\Customer;
/**
 * Class Vendor
 * @package FS\UserBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="user_vendors")
 * @ORM\Entity(repositoryClass="FS\UserBundle\Entity\Repository\VendorRepository")
 *
 * @UniqueEntity(
 *     fields="emailCanonical",
 *     targetClass="FS\UserBundle\Entity\User",
 *     message="This email is already used",
 *     errorPath="email"
 * )
 * @UniqueEntity(
 *     fields="phone",
 *     targetClass="FS\UserBundle\Entity\User",
 *     message="This number is already used"
 * )
 */
class Vendor extends User implements AddressInterface
{

    use AddressTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $contactPersonName;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="vendors", cascade={"persist"})
     * @ORM\JoinColumn(name="c_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="vendor", cascade={"persist"})
     */
    private $employees;

    /**
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\Request", mappedBy="vendor")
     */
    private $requests;

    public function __construct()
    {
        parent::__construct();
        $this->employees = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->addRole("ROLE_VENDOR");
    }

    /**
     * Check access to other users
     *
     * @param User $user
     * @return bool
     */
    public function hasAccessTo(User $user)
    {
        if ($user instanceof Vendor) {
            return $user == $this;
        } elseif ($user instanceof Employee) {
            return $user->getVendor() == $this;
        }

        return false;
    }

    /**
     * Set contactPersonName
     *
     * @param string $contactPersonName
     *
     * @return Vendor
     */
    public function setContactPersonName($contactPersonName)
    {
        $this->contactPersonName = $contactPersonName;

        return $this;
    }

    /**
     * Get contactPersonName
     *
     * @return string
     */
    public function getContactPersonName()
    {
        return $this->contactPersonName;
    }


    /**
     * Add employee
     *
     * @param \FS\UserBundle\Entity\Employee $employee
     *
     * @return Vendor
     */
    public function addEmployee(Employee $employee)
    {
        $this->employees[] = $employee;
        $employee->setVendor($this);
        return $this;
    }

    /**
     * Remove employee
     *
     * @param Employee $employee
     */
    public function removeEmployee(Employee $employee)
    {
        $this->employees->removeElement($employee);
    }

    /**
     * Get employees
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployees()
    {
        return $this->employees;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     * @return $this
     */
    public function setCustomer($customer = null)
    {
        $this->customer = $customer;
        return $this;
    }


    /**
     * Add request
     *
     * @param \FS\TrainingProgramsBundle\Entity\Request $request
     *
     * @return Vendor
     */
    public function addRequest(\FS\TrainingProgramsBundle\Entity\Request $request)
    {
        $this->requests[] = $request;

        return $this;
    }

    /**
     * Remove request
     *
     * @param \FS\TrainingProgramsBundle\Entity\Request $request
     */
    public function removeRequest(\FS\TrainingProgramsBundle\Entity\Request $request)
    {
        $this->requests->removeElement($request);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequests()
    {
        return $this->requests;
    }


    public function hasRequestTo(TrainingProgram $trainingProgram)
    {
        foreach ($this->requests as $request) {
            if ($request->getTrainingProgram()->getId() == $trainingProgram->getId()){
                return true;
            }
        }

        return false;
    }
}
