<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.09.2016
 * Time: 10:16
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Admin
 * @package FS\UserBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="user_extra")
 * @ORM\Entity(repositoryClass="FS\UserBundle\Entity\Repository\AdminRepository")
 */
class Admin extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->addRole("ROLE_ADMIN");
    }

    /**
     * Check access to other users
     *
     * @param User $user
     * @return bool
     */
    public function hasAccessTo(User $user)
    {
        return true;
    }
}