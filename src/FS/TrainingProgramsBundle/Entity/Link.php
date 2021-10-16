<?php

namespace FS\TrainingProgramsBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Table(name="links")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\LinksRepository")
 */
class Link
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $link;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @Assert\LessThanOrEqual(10000)
     */
    protected $trainings;

    /**
     * @var integer
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $trainingsUsed = 0;

    
    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min = 3, max = 64)
     */
    protected $comment;

    /**
     * @ORM\ManyToOne(targetEntity="TrainingProgram", inversedBy="links")
     * @ORM\JoinColumn(name="training_program_id", referencedColumnName="id")
     */
    protected $trainingProgram;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Link
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set trainings
     *
     * @param integer $trainings
     *
     * @return Link
     */
    public function setTrainings($trainings)
    {
        $this->trainings = $trainings;

        return $this;
    }

    /**
     * Get trainings
     *
     * @return integer
     */
    public function getTrainings()
    {
        return $this->trainings;
    }

    /**
     * Set trainingsUsed
     *
     * @param integer $trainingsUsed
     *
     * @return Link
     */
    public function setTrainingsUsed($trainingsUsed)
    {
        $this->trainingsUsed = $trainingsUsed;

        return $this;
    }

    /**
     * Get trainingsUsed
     *
     * @return integer
     */
    public function getTrainingsUsed()
    {
        return $this->trainingsUsed;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Link
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set trainingProgram
     *
     * @param \FS\TrainingProgramsBundle\Entity\TrainingProgram $trainingProgram
     *
     * @return Link
     */
    public function setTrainingProgram(\FS\TrainingProgramsBundle\Entity\TrainingProgram $trainingProgram = null)
    {
        $this->trainingProgram = $trainingProgram;

        return $this;
    }

    /**
     * Get trainingProgram
     *
     * @return \FS\TrainingProgramsBundle\Entity\TrainingProgram
     */
    public function getTrainingProgram()
    {
        return $this->trainingProgram;
    }
}
