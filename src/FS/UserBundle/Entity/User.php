<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 23.09.2016
 * Time: 18:00
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Misd\PhoneNumberBundle\Validator\Constraints as MisdAssert;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class User
 * @package FS\UserBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Entity(repositoryClass="FS\UserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *          "admin" = "Admin",
 *          "vendor" = "Vendor",
 *          "customer" = "Customer",
 *          "employee" = "Employee"
 *      }
 * )
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class User extends BaseUser
{
    const TYPE_ADMIN = 'admin';
    const TYPE_VENDOR = 'vendor';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_EMPLOYEE = 'employee';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="date")
     */

    protected $created;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @Assert\Regex(
     *     pattern="/^.{0,25}$/",
     *     message="Phone number cannot be longer than 25 characters."
     * )
     * @Assert\NotBlank()
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $passwordSetToken;


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     */
    protected $fullName;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;


    public function __construct()
    {
        parent::__construct();
        $this->addRole('ROLE_USER');
    }

    /**
     * Check access to other users
     *
     * @param User $user
     * @return mixed
     */
    public abstract function hasAccessTo(User $user);

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setEmail($email)
    {
        parent::setEmail($email);
        parent::setUsername($email);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return bool
     */
    public function isEmployee()
    {
        return $this->getType() === self::TYPE_EMPLOYEE;
    }

    /**
     * @return string
     */
    public function getType()
    {
        if ($this instanceof Employee) {
            return self::TYPE_EMPLOYEE;
        } elseif ($this instanceof Vendor) {
            return self::TYPE_VENDOR;
        } elseif ($this instanceof Customer) {
            return self::TYPE_CUSTOMER;
        } elseif ($this instanceof Admin) {
            return self::TYPE_ADMIN;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isVendor()
    {
        return $this->getType() === self::TYPE_VENDOR;
    }

    /**
     * @return bool
     */
    public function isCustomer()
    {
        return $this->getType() === self::TYPE_CUSTOMER;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getType() === self::TYPE_ADMIN;
    }

    public function __toString()
    {
        return (string)ucfirst($this->username);
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPasswordSetToken()
    {
        return $this->passwordSetToken;
    }

    /**
     * @param string $passwordSetToken
     */
    public function setPasswordSetToken($passwordSetToken)
    {
        $this->passwordSetToken = $passwordSetToken;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}