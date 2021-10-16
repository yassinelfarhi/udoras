<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 29.09.2016
 * Time: 17:06
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DeleteQueue
 * @package FS\UdorasBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="delete_resource_queue")
 * @ORM\Entity(repositoryClass="FS\UdorasBundle\Entity\Repository\DeleteQueueRepository")
 */
class DeleteQueue
{

    const DELETE_QUEUE__REDIS_RELEASE_CHANNEL = 'udoras:resource:delete';
    const DELETE_QUEUE__REDIS_LOCK_CHANNEL = 'udoras:resource:restore';

    const DELETE_QUEUE__STATUS_COMPLETE = 'complete';
    const DELETE_QUEUE__STATUS_WAIT = 'wait';

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
    protected $lockId;

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
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $resourceId;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $resource;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $status;

    public function __construct(){
        $this->status = self::DELETE_QUEUE__STATUS_WAIT;
    }

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
    public function getLockId()
    {
        return $this->lockId;
    }

    /**
     * @param int $lockId
     */
    public function setLockId($lockId)
    {
        $this->lockId = $lockId;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
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
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}