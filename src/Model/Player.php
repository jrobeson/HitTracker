<?php declare(strict_types=1);

namespace LazerBall\HitTracker\Model;

use Doctrine\ORM\Mapping as ORM;
use LazerBall\HitTracker\Model\Vest;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"game", "unit"},
 *               ignoreNull="true",
 *               message="hittracker.player.unique_unit_required"
 * )
 * @UniqueEntity(fields={"game", "name"},
 *               message="hittracker.game.unique_name_required"
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="game_players",
 *            uniqueConstraints={
 *                @ORM\UniqueConstraint(name="idx_player_game_unit",
 *                                      columns={"game_id", "unit_id"}
 *                )
 *            },
 *            indexes={
 *                @ORM\Index(name="idx_player_game_id", columns={"game_id"}),
 *                @ORM\Index(name="idx_player_unit_id", columns={"unit_id"})
 *            }
 * )
 */
class Player implements ResourceInterface
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $hitPoints;

    /**
     * @var array
     * @ORM\Column(type="json_array", options={"jsonb": "true"})
     */
    private $zoneHits;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $score;

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
     * @ORM\ManyToOne(targetEntity="LazerBall\HitTracker\Model\Vest")
     */
    protected $unit;

    public function __construct(string $name = '', Vest $unit = null, int $hitPoints = 0)
    {
        $this->name = $name;
        $this->team = '';
        $this->unit = $unit;
        $this->hitPoints = $hitPoints;
        $this->score = 0;
        $this->zoneHits = array_fill(1, $unit->getZones(), 0);
        $this->holding = false;

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

    public function setScore(int $score)
    {
        $this->score = $score;
    }

    public function getScore() : int
    {
        return $this->score;
    }

    public function hitsInZone(int $zone) :int
    {
        return $this->zoneHits[$zone];
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

    public function setUnit(Vest $unit)
    {
        $this->unit = $unit;
    }

    public function getUnit() : Vest
    {
        return $this->unit;
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
     * Get Unit Radio Id
     *
     * @return string
     */
    public function getRadioId()
    {
        $this->getUnit()->getRadioId();
    }

    /**
     * Hit a zone
     */
    public function hit(int $zone, int $score = null, int $hitPoints = null)
    {
        // @todo don't depend on unit type here, it's a game type issue
        $handleHitPoints = $this->getUnit()->getUnitType() == 'vest';
        if ($handleHitPoints) {
            if (0 >= $this->getHitPoints()) {
                return;
            }
            $hitPoints = $this->getHitPoints() - $hitPoints;
             $this->setHitPoints($hitPoints);

        }

        if ($score) {
            $score = $this->getScore() + $score;
            $this->setScore($score);
        }

        $this->zoneHits[$zone]++;
    }
}
