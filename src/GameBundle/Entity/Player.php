<?php

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"game", "vest"},
 *               message="This vest was already used on this game"
 * )
 * @UniqueEntity(fields={"game", "name"},
 *               message="There is already a player with that name in this game"
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="game_players",
 *            uniqueConstraints={
 *                @ORM\UniqueConstraint(name="idx_player_game_vest",
 *                                      columns={"game_id", "vest_id"}
 *                )
 *            }
 * )
 */
class Player
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
     * @ORM\Column(name="team", type="string", length=255, nullable=true)
     */
    private $team;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var integer
     * @ORM\Column(name="life_credits", type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThanOrEqual(
     *      value=0,
     *      message="Life credits must be greater than {{ compared_value }}"
     * )
     */
    private $lifeCredits;

    /**
     * @var integer
     * @ORM\Column(name="zone_1", type="integer")
     * @Assert\Type(type="integer")
     */
    private $zone1;

    /**
     * @var integer
     * @ORM\Column(name="zone_2", type="integer")
     * @Assert\Type(type="integer")
     */
    private $zone2;

    /**
     * @var integer
     * @ORM\Column(name="zone_3", type="integer")
     * @Assert\Type(type="integer")
     */
    private $zone3;

    /**
     * @var integer
     * @ORM\Column(name="accuracy", type="integer")
     * @Assert\Type(type="integer")
     */
    private $accuracy;

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
     * @var Game
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="players")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $game;

    /**
     * @var Vest
     * @ORM\ManyToOne(targetEntity="Vest")
     */
    protected $vest;

    /**
     * @param string      $name
     * @param Vest|null   $vest
     * @param int         $lifeCredits
     */
    public function __construct($name = '', Vest $vest = null, $lifeCredits = 0)
    {
        $this->name = $name;
        $this->vest = $vest;
        $this->lifeCredits = $lifeCredits;

        $this->zone1 = 0;
        $this->zone2 = 0;
        $this->zone3 = 0;
        $this->accuracy = 100;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set team
     *
     * @param string $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * Get team
     *
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /** @param integer $lifeCredits */
    public function setLifeCredits($lifeCredits)
    {
        $this->lifeCredits = $lifeCredits;
    }

    /** @return integer */
    public function getLifeCredits()
    {
        return $this->lifeCredits;
    }

    /**
     * @param integer $zone
     * @return integer
     */
    public function hitsInZone($zone)
    {
        $property = 'zone'.$zone;

        return $this->{$property};
    }

    /** @param $accuracy integer */
    public function setAccuracy($accuracy)
    {
        $this->accuracy = $accuracy;
    }

    /** @return integer */
    public function getAccuracy()
    {
        return $this->accuracy;
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

    public function setGame(Game $game)
    {
        $this->game = $game;
    }

    public function setVest(Vest $vest)
    {
        $this->vest = $vest;
    }

    /** @return Vest */
    public function getVest()
    {
        return $this->vest;
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

    /**
     * Get Vest Radio Id
     *
     * @return int
     */
    public function getRadioId()
    {
        $this->getVest()->getRadioId();
    }

    /**
     * Hit a zone
     *
     * @param integer $zone
     * @param integer $lifeCredits
     */
    public function hit($zone, $lifeCredits)
    {
        if (0 >= $this->getLifeCredits()) {
            return;
        }

        $zone = 'zone'.$zone;
        $this->{$zone} = $this->{$zone} + 1;

        $credits = $this->getLifeCredits() - $lifeCredits;
        $this->setLifeCredits($credits);
    }
}
