<?php

namespace HitTracker\CommonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sessions")
 */
class Session
{
    /**
     * @var string
     * @ORM\Column(name="session_id", type="string", length=128)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="session_data", type="binary", nullable=false)
     */
    private $data;

    /**
     * @var integer
     * @ORM\Column(name="session_time", type="integer", nullable=false)
     */
    private $time;

    /**
     * @var integer
     * @ORM\Column(name="session_lifetime", type="integer", nullable=false)
     */
    private $lifetime;

    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getData()
    {
        return $this->data;
    }

    /** @return \DateTime */
    public function getTime()
    {
        return $this->convertToDateTime($this->time);
    }

    /** @return \DateTime */
    public function getLifetime()
    {
        return $this->convertToDateTime($this->lifetime);
    }

    /**
     * @param integer
     * @return \DateTime
     */
    private function convertToDateTime($timeStamp)
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timeStamp);
        return $dateTime;
    }

}
