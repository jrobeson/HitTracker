<?php

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity("radioId")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vests")
 */
class Vest
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="radio_id", type="string", length=8, unique=true)
     * @Assert\Length(min="8", max="8")
     * @Assert\Type(type="xdigit",
     *              message="hittracker.vest.bad_radio_id"
     * )
     */
    private $radioId;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getId()
    {
        return $this->id;
    }

    /** @param string $radioId */
    public function setRadioId($radioId)
    {
        $this->radioId = strtolower($radioId);
    }

    /** @return string */
    public function getRadioId()
    {
        return $this->radioId;
    }

    /** @param bool $active */
    public function setActive($active = true)
    {
        $this->active = $active;
    }

    /** @return bool */
    public function isActive()
    {
        return $this->active;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /** @return \DateTime */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /** @return \DateTime */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /** @ORM\PrePersist */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /** @ORM\PreUpdate */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
