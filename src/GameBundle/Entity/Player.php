<?php

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"game", "vest"},
 *               message="hittracker.player.unique_vest_required"
 * )
 * @UniqueEntity(fields={"game", "name"},
 *               message="hittracker.game.unique_name_required"
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
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $team;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThanOrEqual(
     *      value=0,
     *      message="hittracker.game.not_enough_life_credits"
     * )
     */
    private $lifeCredits;

    /**
     * @var int
     * @ORM\Column(name="zone_1", type="integer")
     * @Assert\Type(type="integer")
     */
    private $zone1;

    /**
     * @var int
     * @ORM\Column(name="zone_2", type="integer")
     * @Assert\Type(type="integer")
     */
    private $zone2;

    /**
     * @var int
     * @ORM\Column(name="zone_3", type="integer")
     * @Assert\Type(type="integer")
     */
    private $zone3;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var Game
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="players")
     * @ORM\JoinColumn(onDelete="CASCADE")
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

    /** @param int $lifeCredits */
    public function setLifeCredits($lifeCredits)
    {
        $this->lifeCredits = $lifeCredits;
    }

    /** @return int */
    public function getLifeCredits()
    {
        return $this->lifeCredits;
    }

    /**
     * @param int $zone
     * @return int
     */
    public function hitsInZone($zone)
    {
        $property = 'zone'.$zone;

        return $this->{$property};
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
     * @param int $zone
     * @param int $lifeCredits
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
