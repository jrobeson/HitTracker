<?php

namespace HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use HitTracker\CommonBundle\Validator\Constraints as CommonAssert;

/**
 * Game
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
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
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

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

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $arena
     */
    public function setArena($arena)
    {
        $this->arena = $arena;
    }

    /**
     * @return int
     */
    public function getArena()
    {
        return $this->arena;
    }

    /**
     * @param int $playerLifeCredits
     */
    public function setPlayerLifeCredits($playerLifeCredits)
    {
        $this->playerLifeCredits = $playerLifeCredits;
    }

    /**
     * @return int
     */
    public function getPlayerLifeCredits()
    {
        return $this->playerLifeCredits;
    }

    /**
     * @param int $lifeCreditsDeducted
     */
    public function setLifeCreditsDeducted($lifeCreditsDeducted)
    {
        $this->lifeCreditsDeducted = $lifeCreditsDeducted;
    }

    /**
     * @return int
     */
    public function getLifeCreditsDeducted()
    {
        return $this->lifeCreditsDeducted;
    }

    /**
     * Set the time the game ends
     *
     * @param \DateTime $endsAt
     */
    public function setEndsAt(\DateTime $endsAt)
    {
        $this->endsAt = $endsAt;
    }

    /**
     * Get the time the game ends
     *
     * @return \DateTime
     */
    public function getEndsAt()
    {
        return $this->endsAt;
    }

    /**
     * Set the time the game was created
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the date the game was created
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the date the game was updated
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get the date the game was updated
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the length of a game in minutes
     *
     * @param integer $time time in minutes
     */
    public function setGameLength($time)
    {
        $this->gameLength = $time;
        $dateTime = new \DateTime('+' . $time . ' minutes');
        $this->setEndsAt($dateTime);
    }

    /**
     * @return array the length of the game in minutes and seconds
     *
     */
    public function getGameLength()
    {
        if (empty($this->gameLength) && !empty($this->createdAt)) {
            $parts = $this->timeTotal();
            return [
                'minutes' => ($parts->h * 60) + $parts->m,
                'seconds' => $parts->s
            ];
        }

        return $this->gameLength;
    }

    /**
     * Is the Game currently active?
     *
     * @return bool
     */
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

    /**
     * Get how much time is left in the game
     *
     * @return \DateInterval
     */
    public function timeLeft()
    {
        $now = new \DateTime();
        return $now->diff($this->endsAt);
    }

    /**
     * Get the total time of the game
     *
     * @return \DateInterval
     */
    public function timeTotal()
    {
        return $this->endsAt->diff($this->createdAt);
    }

    /**
     * Add a player to the game
     *
     * @param Player $player
     */
    public function addPlayer(Player $player)
    {
        $this->players->add($player);
        $player->setGame($this);
    }

    /**
     * Set players
     *
     * @param Collection $players
     */
    public function setPlayers(Collection $players)
    {
        $this->players = $players;
    }

    /**
     * Get players
     *
     * @return Collection of all Players
     */
    public function getPlayers()
    {
        return $this->players;
    }

   /**
     * @return Collection
     */
    public function getPlayerByEsn($esn)
    {
        $players = $this->getPlayers()->filter(function ($player) use ($esn) {
            return $player->getVest()->getEsn() == $esn;
        });
        return $players->first();
    }

    /**
     * @param $team
     *
     * @return Collection
     */
    public function getPlayersByTeam($team)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('team', $team));

        return $this->getPlayers()->matching($criteria);
    }

    public function getTeams()
    {
        $teams = array_unique(
            $this->getPlayers()->map(function ($player) {
            return $player->getTeam();
        })->toArray());

        return $teams;
    }

    public function getTeamScore($team)
    {
        $team = $this->getPlayersByTeam($team);
        $score = array_sum($team->map(function ($player) {
                return $player->getLifeCredits();
            })->toArray());

        return $score;
    }

    public function getTotalScore()
    {
        $score = array_sum($this->getPlayers()->map(function($player) {
            return $player->getLifeCredits();
        })->toArray());

        return $score;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
}
