<?php declare(strict_types=1);

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as Serializer;
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
 *                @ORM\Index(name="idx_player_team_id", columns={"match_team_id"}),
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
     * @Serializer\Groups({"read"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Serializer\Groups({"read","write"})
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"read","write"})
     */
    private $hitPoints;

    /**
     * @var array
     * @ORM\Column(type="json_array", options={"jsonb": "true"})
     * @Serializer\Groups({"read","write"})
     */
    public $zoneHits;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"read","write"})
     */
    private $score;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Game")
     */
    private $game;

    /**
     * @var MatchTeam
     * @ORM\ManyToOne(targetEntity="MatchTeam", inversedBy="players")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $matchTeam;

    /**
     * @var Vest
     * @ORM\ManyToOne(targetEntity="App\Model\Vest")
     * @Serializer\Groups({"read","write"})
     */
    protected $unit;

    public function __construct(string $name = '', Vest $unit, int $hitPoints = 0)
    {
        $this->name = $name;
        $this->unit = $unit;
        $this->hitPoints = $hitPoints;
        $this->score = 0;
        $this->zoneHits = [0];
        if (null !== $unit->getZones()) {
            $this->zoneHits = array_fill(1, $unit->getZones(), 0);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTeam(MatchTeam $team): void
    {
        $this->matchTeam = $team;
    }

    public function getTeam(): MatchTeam
    {
        return $this->matchTeam;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setHitPoints(int $hitPoints): void
    {
        $this->hitPoints = $hitPoints;
    }

    public function getHitPoints(): int
    {
        return $this->hitPoints;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function hitsInZone(int $zone): int
    {
        return $this->zoneHits[$zone];
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getUnit(): Vest
    {
        return $this->unit;
    }

    /** @ORM\PrePersist */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    /** @ORM\PreUpdate */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getRadioId(): ?string
    {
        return $this->getUnit()->getRadioId();
    }

    /**
     * Hit a zone
     */
    public function hit(int $zone, int $score = null, int $hitPoints = null): void
    {
        // @todo don't depend on unit type here, it's a game type issue
        $handleHitPoints = 'vest' === $this->getUnit()->getUnitType();
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
