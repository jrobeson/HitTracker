<?php

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LazerBall\HitTracker\CommonBundle\Validator\Constraints as CommonAssert;

/**
 * Game
 *
 * @ORM\Entity
 * @ORM\Table(name="games")
 */
class Game
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     * @ORM\Column(name="arena", type="integer")
     * @Assert\Type("numeric")
     * @Assert\NotBlank()
     */
    private $arena;

    /**
     * @var integer
     * @ORM\Column(name="player_life_credits", type="integer")
     * @Assert\Type("integer")
     * @Assert\NotBlank()
     */
    private $playerLifeCredits;

    /**
     * @var integer
     * @ORM\Column(name="life_credits_deducted", type="integer")
     * @Assert\Type("integer")
     * @Assert\NotBlank()
     */
    private $lifeCreditsDeducted;

    /**
     * @var \DateTime
     * @ORM\Column(name="ends_at", type="datetime")
     */
    private $endsAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var Collection
     *
     * @Assert\Valid()
     * @Assert\Count(min="2",
     *               minMessage="A Game must have at least two players"
     * )
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="vest",
     *         message="You can't use the same vest twice in a game.")
     * })
     * @Assert\All(constraints={
     *     @CommonAssert\UniqueCollectionField(
     *         propertyPath="name",
     *         message="You can't add a player with the same name to a game."
     *     )
     * })
     * @ORM\OneToMany(targetEntity="Player", mappedBy="game",
     *                cascade={"persist", "remove"})
     * @ORM\OrderBy({"team" = "ASC", "id" = "ASC"})
     */
    protected $players;

    /**
     * @var integer
     * @Assert\Range(min=1)
     * @Assert\Type("integer")
     */
    protected $gameLength;

    public function __construct()
    {
        $this->arena = 1;
        $this->players = new ArrayCollection();
    }

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    /** @param int $arena */
    public function setArena($arena)
    {
        $this->arena = $arena;
    }

    /** @return int */
    public function getArena()
    {
        return $this->arena;
    }

    /** @param int $playerLifeCredits */
    public function setPlayerLifeCredits($playerLifeCredits)
    {
        $this->playerLifeCredits = $playerLifeCredits;
    }

    /** @return int */
    public function getPlayerLifeCredits()
    {
        return $this->playerLifeCredits;
    }

    /** @param int $lifeCreditsDeducted */
    public function setLifeCreditsDeducted($lifeCreditsDeducted)
    {
        $this->lifeCreditsDeducted = $lifeCreditsDeducted;
    }

    /** @return number */
    public function getLifeCreditsDeducted()
    {
        return $this->lifeCreditsDeducted;
    }

    /** @param \DateTime $endsAt */
    public function setEndsAt(\DateTime $endsAt)
    {
        $this->endsAt = $endsAt;
    }

    /** @return \DateTime */
    public function getEndsAt()
    {
        return $this->endsAt;
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

    /**
     * Set the length of a game in minutes
     *
     * @param integer $minutes time in minutes
     */
    public function setGameLength($minutes)
    {
        $this->gameLength = (int)$minutes;

        $start = new \DateTime();
        $this->setCreatedAt($start);
        $end = clone $start;
        $end->add(new \DateInterval('PT'.$this->gameLength.'M'));
        $this->setEndsAt($end);
    }

    /** @return array the length of the game in minutes and seconds */
    public function getGameLength()
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

    /** @return bool */
    public function isActive()
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

    /** @return \DateInterval */
    public function timeLeft()
    {
        $now = new \DateTime();
        return $now->diff($this->endsAt);
    }

    /** @return \DateInterval */
    public function timeTotal()
    {
        return $this->endsAt->diff($this->createdAt);
    }

    public function addPlayer(Player $player)
    {
        $this->players->add($player);
        $player->setGame($this);
    }

    /** @param Collection $players */
    public function setPlayers(Collection $players)
    {
        $this->players = $players;
    }

    /** @return Collection */
    public function getPlayers()
    {
        return $this->players;
    }

   /** @return Player */
    public function getPlayerByRadioId($radioId)
    {
        $players = $this->getPlayers()->filter(function ($player) use ($radioId) {
            return $player->getVest()->getRadioId() == $radioId;
        });
        return $players->first();
    }

    /**
     * @param string $team
     *
     * @return Collection
     */
    public function getPlayersByTeam($team)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('team', $team));

        return $this->getPlayers()->matching($criteria);
    }

    /** @return array */
    public function getTeams()
    {
        $teams = array_unique(
            $this->getPlayers()->map(function ($player) {
            return $player->getTeam();
        })->toArray());

        return $teams;
    }

    /**
     * @param $team
     * @return number
     */
    public function getTeamScore($team)
    {
        $team = $this->getPlayersByTeam($team);
        $score = array_sum($team->map(function ($player) {
                return $player->getLifeCredits();
            })->toArray());

        return $score;
    }

    /** @return number */
    public function getTotalScore()
    {
        $score = array_sum($this->getPlayers()->map(function($player) {
            return $player->getLifeCredits();
        })->toArray());

        return $score;
    }
}
