<?php declare(strict_types=1);

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
     * @var bool
     * @ORM\Column(name="holding", type="boolean")
     * @Assert\Type(type="boolean")
     */
    private $holding;

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

    public function __construct(string $name = '', Vest $vest = null, int $hitPoints = 0)
    {
        $this->name = $name;
        $this->team = '';
        $this->vest = $vest;
        $this->hitPoints = $hitPoints;
        $this->holding = false;

        $this->zone1 = 0;
        $this->zone2 = 0;
        $this->zone3 = 0;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setTeam(string $team)
    {
        $this->team = $team;
    }

    public function getTeam() : string
    {
        return $this->team;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setHitPoints(int $hitPoints)
    {
        $this->hitPoints = $hitPoints;
    }

    public function getHitPoints() : int
    {
        return $this->hitPoints;
    }

    public function hitsInZone(int $zone) :int
    {
        $property = 'zone'.$zone;

        return $this->{$property};
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt() : \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt() : \DateTime
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

    public function getVest() : Vest
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
     * @return string
     */
    public function getRadioId()
    {
        $this->getVest()->getRadioId();
    }

    /**
     * Hit a zone
     */
    public function hit(int $zone, int $hitPoints)
    {
        if (0 >= $this->getHitPoints()) {
            return;
        }

        $zone = 'zone'.$zone;
        $this->{$zone} = $this->{$zone} + 1;

        $hitPoints = $this->getHitPoints() - $hitPoints;
        $this->setHitPoints($hitPoints);
    }

    public function isHolding() : bool
    {
        return $this->holding;
    }

    public function setHolding(bool $holding)
    {
        $this->holding = $holding;
    }

    public function hold(int $penalty = 0)
    {
        $this->setHolding(true);
        $this->setHitPoints($this->getHitPoints() - $penalty);
    }
}
