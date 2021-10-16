<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 28.09.2016
 * Time: 12:15
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Lock
 * @package FS\UdorasBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="locks_resource")
 * @ORM\Entity(repositoryClass="FS\UdorasBundle\Entity\Repository\LockRepository")
 */
class Lock
{
    const LOCK__STATUS_LOCKED = 'locked';
    const LOCK__STATUS_FREE = 'free';

    const LOCK_RESOURCE__REDIS_RELEASE_CHANNEL = 'udoras:resource:release';
    const LOCK_RESOURCE__REDIS_LOCK_CHANNEL = 'udoras:resource:lock';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $userId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $sessionId;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     * Resource is [CUSTOMER, EMPLOYEE, VENDOR]
     *
     */
    protected $resource;

    /**
     * @var string
     * @ORM\Column(type="integer")
     */
    protected $resourceId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $status;

    public function __construct(){}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param string $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }
}