<?php

namespace FS\TrainingProgramsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FS\TrainingProgramsBundle\Validator\Constraints as FSAssert;
use FS\UserBundle\Entity\Customer;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TrainingProgram
 * @package FS\TrainingProgramsBundle\Entity
 * @author <vladislav@fora-soft.com>
 *
 * @ORM\Table(name="training_programs")
 * @ORM\Entity(repositoryClass="FS\TrainingProgramsBundle\Entity\Repository\TrainingProgramRepository")
 *
 * @FSAssert\UniqueForUser(
 *     errorPath="title",
 * )
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class TrainingProgram
{

    const STATE_CREATED = 'created';
    const STATE_EDIT = 'edit';
    const STATE_SAVED = 'saved';


    const MAX_NAME_STRING_LEN = 45;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(name="link", type="string", nullable=true)
     */
    protected $link;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\LessThanOrEqual(100)
     * @Assert\GreaterThanOrEqual(1)
     */
    protected $passing;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @Assert\Range(min=0, max=1000000)
     * @Assert\NotBlank()
     */
    protected $price = 0;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="FS\UserBundle\Entity\Customer", inversedBy="trainingPrograms")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $customer;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\Slide", mappedBy="program", cascade={"all"})
     */
    protected $slides;

    /**
     * @ORM\OneToMany(targetEntity="Link", mappedBy="trainingProgram", cascade={"all"})
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\Request", mappedBy="trainingProgram", cascade={"all"})
     */
    protected $requests;

    /**
     * @ORM\OneToMany(targetEntity="FS\TrainingProgramsBundle\Entity\Access", mappedBy="trainingProgram", cascade={"all"})
     */
    protected $accesses;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @var integer
     * @ORM\Column(name="certificate_valid_months", nullable=true, type="integer")
     * @Assert\GreaterThanOrEqual(1)
     * @FSAssert\NotBlankOneOf(otherField="certificateValidUntil")
     */
    protected $certificateValidMonths = 24;
    /**
     * @var \DateTime
     * @ORM\Column(name="certificate_valid_until", nullable=true, type="datetime")
     * @FSAssert\NotBlankOneOf(otherField="certificateValidMonths")
     */
    protected $certificateValidUntil = null;

    /**
     * TrainingProgram constructor.
     */
    public function __construct()
    {
        $this->state = self::STATE_CREATED;
        $this->slides = new ArrayCollection();
        $this->links = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->accesses = new ArrayCollection();
        $this->link = uniqid('', true);
        $this->passing = 60;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set customer
     *
     * @param Customer $customer
     *
     * @return TrainingProgram
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getPassing()
    {
        if ($this->getQuestionsSlides()->count() > 0 || $this->getSlides()->count() === 0) {
            return $this->passing;
        }
        return 0;

    }

    /**
     * @param int $passing
     * @return $this
     */
    public function setPassing($passing)
    {
        if ($this->getQuestionsSlides()->count() > 0 || $this->getSlides()->count() === 0) {
            $this->passing = $passing;
        }
        return $this;
    }

    /**
     * @return Collection
     */
    public function getQuestionsSlides()
    {
        return $this->getSlides()
            ->filter(function (Slide $slide) {
                return $slide->getSlideType() === Slide::SLIDE_TYPE__QUESTION;

            });
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getSlides()
    {
        return $this->slides;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @param Slide $slide
     * @return $this
     */
    public function addSlide(Slide $slide)
    {
        $this->slides[] = $slide;
        $slide->setProgram($this);

        return $this;
    }

    /**
     * @param Slide $slide
     * @return $this
     */
    public function removeSlide(Slide $slide)
    {
        $this->slides->removeElement($slide);
        return $this;
    }

    /**
     * Check if trainingProgram has slides with questions
     *
     * @return bool
     */
    public function hasQuestions()
    {
        foreach ($this->slides as $slide) {
            if ($slide->getSlideType() === Slide::SLIDE_TYPE__QUESTION) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return TrainingProgram
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Add link
     *
     * @param Link $link
     *
     * @return TrainingProgram
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;

        return $this;
    }

    /**
     * Remove link
     *
     * @param Link $link
     */
    public function removeLink(Link $link)
    {
        $this->links->removeElement($link);
    }

    /**
     * Get links
     *
     * @return Collection
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Add request
     *
     * @param Request $request
     *
     * @return TrainingProgram
     */
    public function addRequest(Request $request)
    {
        $this->requests[] = $request;

        return $this;
    }

    /**
     * Remove request
     *
     * @param Request $request
     */
    public function removeRequest(Request $request)
    {
        $this->requests->removeElement($request);
    }

    /**
     * Get requests
     *
     * @return Collection
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Add access
     *
     * @param Access $access
     *
     * @return TrainingProgram
     */
    public function addAccess(Access $access)
    {
        $this->accesses[] = $access;

        return $this;
    }

    /**
     * Remove access
     *
     * @param Access $access
     */
    public function removeAccess(Access $access)
    {
        $this->accesses->removeElement($access);
    }

    /**
     * Get accesses
     *
     * @return Collection
     */
    public function getAccesses()
    {
        return $this->accesses;
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

    /**
     * @return int
     */
    public function getCertificateValidMonths()
    {
        return $this->certificateValidMonths;
    }

    /**
     * @param int $certificateValidMonths
     * @return TrainingProgram
     */
    public function setCertificateValidMonths($certificateValidMonths)
    {
        $this->certificateValidMonths = $certificateValidMonths;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCertificateValidUntil()
    {
        return $this->certificateValidUntil;
    }

    /**
     * @param \DateTime $certificateValidUntil
     * @return TrainingProgram
     */
    public function setCertificateValidUntil($certificateValidUntil)
    {
        $this->certificateValidUntil = $certificateValidUntil;
        return $this;
    }
}
