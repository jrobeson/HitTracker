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
 *            },
 *            indexes={
 *                @ORM\Index(name="idx_player_game_id", columns={"game_id"}),
 *                @ORM\Index(name="idx_player_vest_id", columns={"vest_id"})
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
     *      message="hittracker.game.not_enough_hit_points"
     * )
     */
    private $hitPoints;

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
     * @param int         $hitPoints
     */
    public function __construct($name = '', Vest $vest = null, $hitPoints = 0)
    {
        $this->name = $name;
        $this->vest = $vest;
        $this->hitPoints = $hitPoints;

        $this->zone1 = 0;
        $this->zone2 = 0;
        $this->zone3 = 0;
    }

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /** @param string $team */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /** @return string */
    public function getTeam()
    {
        return $this->team;
    }

    /** @param string $name */
    public function setName($name)
    {
        $this->name = $name;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @param int $hitPoints */
    public function setHitPoints($hitPoints)
    {
        $this->hitPoints = $hitPoints;
    }

    /** @return int */
    public function getHitPoints()
    {
        return $this->hitPoints;
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
     * @param int $hitPoints
     */
    public function hit($zone, $hitPoints)
    {
        if (0 >= $this->getHitPoints()) {
            return;
        }

        $zone = 'zone'.$zone;
        $this->{$zone} = $this->{$zone} + 1;

        $hitPoints = $this->getHitPoints() - $hitPoints;
        $this->setHitPoints($hitPoints);
    }
}
