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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Slide
 * @package FS\TrainingProgramsBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="slide")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\SlideRepository")
 */
class Slide
{

    const SLIDE_TYPE__BLANK = 'blank';
    const SLIDE_TYPE__TEXT = 'text';
    const SLIDE_TYPE__IMAGE = 'image';
    const SLIDE_TYPE__VIDEO = 'video';
    const SLIDE_TYPE__AUDIO = 'audio';
    const SLIDE_TYPE__QUESTION = 'question';

    /**
     * Command slides
     */
    const SLIDE_TYPE__NO_SLIDE = 'no_slide';
    const SLIDE_TYPE__REDIRECT = 'redirect';
    const SLIDE_TYPE__PAUSE = 'pause';

    const SLIDE_TYPE_FINAL_QUESTION = 'final_question';
    const SLIDE_TYPE__ANSWERED_QUESTION = 'answered_question';

    const SLIDE_STATE__SAVED = 'saved';
    const SLIDE_STATE__NOT_SAVED = 'not saved';

    const SLIDE_MARKER__MARKED_FOR_DELETE = 'marked_delete';
    const SLIDE_MARKER__DELETED = 'deleted';
    const SLIDE_MARKER__NO = 'no';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * serializer
     * @Groups({"presentationCreate", "training"})
     *
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     * @Groups({"presentationCreate"})
     */
    protected $state;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     * serialize
     * @Groups({"presentationCreate","training"})
     */
    protected $slideType;

    /**
     * @ORM\ManyToOne(targetEntity="FS\TrainingProgramsBundle\Entity\TrainingProgram", inversedBy="slides")
     * @ORM\JoinColumn(name="trp_id", referencedColumnName="id")
     */
    protected $program;


    /**
     * @var string
     * @ORM\Column(type="text", nullable=true, length=1200)
     *
     * serialize
     * @Groups({"presentationCreate"})
     */
    protected $slideData;

    /**
     * @var string
     * @ORM\Column(type="json_array", nullable=true)
     * serialize
     * @Groups({"presentationCreate"})
     */
    protected $extraFields;


    /**
     * @var string
     * @ORM\Column(type="integer")
     * @Groups({"presentationCreate","training"})
     */
    protected $realNum;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Groups({"presentationCreate","training"})
     */
    protected $timeLimit;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Groups({"presentationCreate"})
     */
    protected $marker;


    /**
     * Slide constructor.
     */
    public function __construct()
    {
        $this->timeLimit = 0;
        $this->realNum = 1;
        $this->state = self::SLIDE_STATE__NOT_SAVED;
        $this->slideType = self::SLIDE_TYPE__BLANK;
        $this->marker = self::SLIDE_MARKER__NO;
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
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @param mixed $program
     * @return $this
     */
    public function setProgram(TrainingProgram $program = null)
    {
        $this->program = $program;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlideType()
    {
        return $this->slideType;
    }

    /**
     * @param mixed $slideType
     */
    public function setSlideType($slideType)
    {
        $this->slideType = $slideType;
    }

    /**
     * @return string
     */
    public function getSlideData()
    {
        return $this->slideData;
    }

    /**
     * @param string $slideData
     */
    public function setSlideData($slideData)
    {
        $this->slideData = $slideData;
    }

    /**
     * @return integer
     */
    public function getRealNum()
    {
        return $this->realNum;
    }

    /**
     * @param integer $realNum
     */
    public function setRealNum($realNum)
    {
        $this->realNum = $realNum;
    }

    /**
     * @return string
     */
    public function getExtraFields()
    {
        return $this->extraFields;
    }

    /**
     * @param string $extraFields
     */
    public function setExtraFields($extraFields)
    {
        $this->extraFields = $extraFields;
    }

    /**
     * @return integer
     */
    public function getTimeLimit()
    {
        return $this->timeLimit;
    }

    /**
     * @param integer $timeLimit
     */
    public function setTimeLimit($timeLimit)
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * @return string
     */
    public function getMarker()
    {
        return $this->marker;
    }

    /**
     * @param string $marker
     */
    public function setMarker($marker)
    {
        $this->marker = $marker;
    }
}