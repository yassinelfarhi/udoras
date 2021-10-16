<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 14.10.2016
 * Time: 13:22
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Model\Address;


/**
 * Class AddressTrait
 * @package FS\UserBundle\Model\Address
 * @author <vladislav@fora-soft.com>
 */
trait AddressTrait
{

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     */
    protected $street;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $state;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $zipCode;


    /**
     * Set street
     *
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return $this
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return $this
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }
}