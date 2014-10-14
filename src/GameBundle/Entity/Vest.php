<?php

namespace HitTracker\GameBundle\Entity;

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
     *              message="The Radio Id is not formatted correctly"
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
     * Set Radio Id
     *
     * @param string $radioId
     */
    public function setRadioId($radioId)
    {
        $this->radioId = strtolower($radioId);
    }

    /**
     * Get Radio Id
     *
     * @return string
     */
    public function getRadioId()
    {
        return $this->radioId;
    }

    /**
     * Set if the vest is active or not
     *
     * @param bool $active
     */
    public function setActive($active = true)
    {
        $this->active = $active;
    }

    /**
     * Check whether the vest is active or not
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set date the vest was created
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get date the vest was created
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set date the vest was updated
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get date the vest was updated
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
