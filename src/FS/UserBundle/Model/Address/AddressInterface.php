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
 * Interface AddressInterface
 * @package FS\UserBundle\Model\Address
 */
interface AddressInterface
{
    /**
     * Set street
     *
     * @param string $street
     *
     */
    public function setStreet($street);

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet();

    /**
     * Set city
     *
     * @param string $city
     *
     */
    public function setCity($city);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set state
     *
     * @param string $state
     *
     */
    public function setState($state);

    /**
     * Get state
     *
     * @return string
     */
    public function getState();

    /**
     * Set country
     *
     * @param string $country
     *
     */
    public function setCountry($country);

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     */
    public function setZipCode($zipCode);

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode();
}