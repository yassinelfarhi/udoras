<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 06.10.2016
 * Time: 17:47
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\TrainingProgramsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Slide
 * @package FS\TrainingProgramsBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="encoding_queue")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\EncodingQueueItemRepository")
 */
class EncodingQueueItem
{
    const NOT_ENCODED = 'not_encoded';
    const ENCODED = 'encoded';
    const ERROR = 'error';

    const VIDEO = 'video';
    const AUDIO = 'audio';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     **
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $encodingStatus;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="from_path")
     */
    protected $from;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="to_path")
     */
    protected $to;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="url")
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="item_type")
     */
    protected $type;


    /**
     * Slide constructor.
     */
    public function __construct()
    {
        $this->encodingStatus = self::NOT_ENCODED;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEncodingStatus()
    {
        return $this->encodingStatus;
    }

    /**
     * @param string $encodingStatus
     * @return EncodingQueueItem
     */
    public function setEncodingStatus($encodingStatus)
    {
        $this->encodingStatus = $encodingStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return EncodingQueueItem
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return EncodingQueueItem
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return EncodingQueueItem
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return EncodingQueueItem
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}