<?php declare(strict_types=1);

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use LazerBall\HitTracker\CommonBundle\Validator\Constraints as CommonAssert;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Game
 *
 * @ORM\Entity
 * @ORM\Table(name="games")
 */
class Game
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @todo cap the upper bound on arenas based on how many there really are.
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type("numeric")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.arena_not_exists"
     * )
     */
    private $arena;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_enough_hit_points"
     * )
     */
    private $playerHitPoints;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_enough_deducted_hit_points"
     * )
     */
    private $playerHitPointsDeducted;

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
     * @Assert\Count(min="2",
     *               minMessage="hittracker.game.not_enough_players"
     * )
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="vest",
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
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_long_enough"
     * )
     */
    protected $gameLength;

    public function __construct()
    {
        $this->arena = 1;
        $this->players = new ArrayCollection();
        $this->gameLength = [0, 0];
        $this->playerHitPoints = 0;
        $this->playerHitPointsDeducted = 0;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setArena(int $arena)
    {
        $this->arena = $arena;
    }

    public function getArena() : int
    {
        return $this->arena;
    }

    public function setPlayerHitPoints(int $playerHitPoints)
    {
        $this->playerHitPoints = $playerHitPoints;
    }

    public function getPlayerHitPoints() : int
    {
        return $this->playerHitPoints;
    }

    public function setPlayerHitPointsDeducted(int $playerHitPointsDeducted)
    {
        $this->playerHitPointsDeducted = $playerHitPointsDeducted;
    }

    public function getPlayerHitPointsDeducted() : int
    {
        return $this->playerHitPointsDeducted;
    }

    public function setEndsAt(\DateTime $endsAt)
    {
        $this->endsAt = $endsAt;
    }

    public function getEndsAt() : \DateTime
    {
        return $this->endsAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt() : \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the length of a game in minutes
     */
    public function setGameLength(int $minutes)
    {
        $this->gameLength = (int) $minutes;

        $start = new \DateTime();
        $this->setCreatedAt($start);
        $end = clone $start;
        $end->add(new \DateInterval('PT'.$this->gameLength.'M'));
        $this->setEndsAt($end);
    }

    /** @return array the length of the game in minutes and seconds */
    public function getGameLength() : array
    {
        if (empty($this->gameLength) && !empty($this->createdAt)) {
            $parts = $this->timeTotal();

            return [
                'hours' => $parts->h,
                'minutes' => $parts->i,
            ];
        }

        return $this->gameLength;
    }

    public function isActive() : bool
    {
        return $this->endsAt > new \DateTime();
    }

    /**
     * Mark the game as stopped
     *
     * Sets endsAt to now
     */
    public function stop()
    {
        $this->endsAt = new \DateTime();
    }

    public function timeLeft() : \DateInterval
    {
        $now = new \DateTime();

        return $now->diff($this->endsAt);
    }

    public function timeTotal() : \DateInterval
    {
        return $this->endsAt->diff($this->createdAt);
    }

    public function addPlayer(Player $player)
    {
        $this->players->add($player);
        $player->setGame($this);
    }

    public function setPlayers(Collection $players)
    {
        $this->players = $players;
    }

    public function getPlayers() : Collection
    {
        return $this->players;
    }

    public function getPlayerByRadioId($radioId) : Player
    {
        $players = $this->getPlayers()->filter(function (Player $player) use ($radioId) {
            return $player->getVest()->getRadioId() == $radioId;
        });

        return $players->first();
    }

    public function getPlayersByTeam(string $team) : Collection
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('team', $team));

        return $this->getPlayers()->matching($criteria);
    }

    public function getTeams() : array
    {
        $teams = array_unique(
            $this->getPlayers()->map(function (Player $player) {
            return $player->getTeam();
        })->toArray());

        return $teams;
    }

    public function getTeamHitPoints(string $team) : int
    {
        $team = $this->getPlayersByTeam($team);
        $score = array_sum($team->map(function (Player $player) {
                return $player->getHitPoints();
            })->toArray());

        return $score;
    }

    public function getTotalHitPoints() : int
    {
        $score = array_sum($this->getPlayers()->map(function (Player $player) {
            return $player->getHitPoints();
        })->toArray());

        return $score;
    }
}
