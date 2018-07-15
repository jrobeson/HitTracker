<?php declare(strict_types=1);

namespace App\Model;

use App\Validator\Constraints as CommonAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Game
 *
 * @ORM\Entity
 * @ORM\Table(name="games")
 */
class Game implements ResourceInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.arena_not_exists"
     * )
     */
    private $arena;

    /** @ORM\Column(type="json_document", options={"jsonb": "true"}) */
    private $settings;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $endsAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var Collection
     *
     * @Assert\Valid(traverse=true)
     * @Assert\Count(min="1",
     *               minMessage="hittracker.game.not_enough_players"
     * )
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="unit",
     *         message="hittracker.game.unique_vests_required")
     * })
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="name",
     *         message="hittracker.game.unique_names_required"
     *     )
     * })
     * @ORM\OneToMany(targetEntity="Player", mappedBy="game",
     *                cascade={"persist", "remove"})
     * @ORM\OrderBy({"team" = "ASC", "id" = "ASC"})
     */
    protected $players;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_long_enough"
     * )
     */
    protected $length;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\Choice(callback="getGameTypes")
     */
    protected $gameType;

    public function __construct(string $gameType, int $length, MatchSettings $settings, int $arena = 1, ?\DateTime $createdAt = null)
    {
        $this->arena = $arena;
        $this->gameType = $gameType;
        $this->players = new ArrayCollection();
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->setGameLength($length);
        $this->settings = $settings;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getArena(): int
    {
        return $this->arena;
    }

    public function getEndsAt(): \DateTime
    {
        return $this->endsAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the length of a game in minutes
     */
    private function setGameLength(int $minutes): void
    {
        $this->length = $minutes;

        $end = clone $this->createdAt;
        $end->add(new \DateInterval('PT'.$this->length.'M'));
        $this->endsAt = $end;
    }

    /**
     * @return int the length of the game in minutes
     */
    public function getGameLength(): int
    {
        return $this->length;
    }

    /** @return string[] */
    public static function getHumanGameTypes(): array
    {
        return array_map(function ($t) {
            return ucwords(str_replace('_', ' ', $t));
        }, self::getGameTypes());
    }

    /** @return string[] */
    public static function getGameTypes(): array
    {
        return ['team', 'target'];
    }

    public function getGameType(): ?string
    {
        return $this->gameType;
    }

    public function getSettings(): MatchSettings
    {
        return $this->settings;
    }

    public function isActive(): bool
    {
        return $this->endsAt > new \DateTime();
    }

    /**
     * Mark the game as stopped
     *
     * Sets endsAt to now
     */
    public function stop(): void
    {
        $this->endsAt = new \DateTime();
    }

    public function timeLeft(): \DateInterval
    {
        $now = new \DateTime();

        return $now->diff($this->endsAt);
    }

    public function timeTotal(): \DateInterval
    {
        return $this->endsAt->diff($this->createdAt);
    }

    public function addPlayer(Player $player): void
    {
        $this->players->add($player);
        $player->setGame($this);
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @return Player|mixed
     */
    public function getPlayerByRadioId(string $radioId)
    {
        $players = $this->getPlayers()->filter(function (Player $player) use ($radioId) {
            return $player->getUnit()->getRadioId() === $radioId;
        });

        return $players->first();
    }

    /** @return string[] */
    public function getTeamPlayers(): array
    {
        $players = [];
        foreach ($this->getTeams() as $team) {
            $players[$team][] = $this->getPlayersByTeam($team);
        }

        return $players;
    }

    public function getPlayersByTeam(string $team): Collection
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('team', $team));

        return $this->getPlayers()->matching($criteria);
    }

    /** @return string[] */
    public function getTeams(): array
    {
        $teams = array_unique(
            $this->getPlayers()->map(function (Player $player) {
                return $player->getTeam();
            })->toArray());
        // reindex array
        return array_values($teams);
    }

    public function getTeamHitPoints(string $team): int
    {
        $team = $this->getPlayersByTeam($team);
        $teamHP = array_sum($team->map(function (Player $player) {
            return $player->getHitPoints();
        })->toArray());

        return (int) $teamHP;
    }

    public function getTotalHitPoints(): int
    {
        $totalHP = array_sum($this->getPlayers()->map(function (Player $player) {
            return $player->getHitPoints();
        })->toArray());

        return (int) $totalHP;
    }

    public function getTeamScore(string $team): int
    {
        $team = $this->getPlayersByTeam($team);
        $score = array_sum($team->map(function (Player $player) {
            return $player->getScore();
        })->toArray());

        return (int) $score;
    }

    public function getTotalScore(): int
    {
        $score = array_sum($this->getPlayers()->map(function (Player $player) {
            return $player->getScore();
        })->toArray());

        return (int) $score;
    }
}
