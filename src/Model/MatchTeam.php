<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 Johnny Robeson <johnny@localmomentum.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="match_teams",
 *            uniqueConstraints={
 *                @ORM\UniqueConstraint(name="idx_match_team_name",
 *                                      columns={"game_id", "name"}
 *                ),
 *            },
 *            indexes={
 *                @ORM\Index(name="idx_match_team_game_id", columns={"game_id"}),
 *            }
 * )
 */
class MatchTeam implements ResourceInterface
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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $color;

    /**
     * @var Game
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="teams")
     */
    private $game;

    /**
     * @var Collection
     *
     * @Assert\Valid(traverse=true)
     * @Assert\Count(min="1",
     *               minMessage="hittracker.game.not_enough_players_on_team"
     * )
     * @ORM\OneToMany(
     *   targetEntity="Player",
     *   mappedBy="matchTeam",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $players;

    public function __construct(string $name, string $color)
    {
        $this->name = $name;
        $this->color = $color;
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    /** @param array<Player> $players */
    public function setPlayers(array $players): void
    {
        $this->players = new ArrayCollection($players);
    }

    public function addPlayer(Player $player): void
    {
        $this->players->add($player);
        $player->setTeam($this);
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getScore(): int
    {
        return (int) array_sum($this->players->map(function (Player $player) {
            return $player->getScore();
        })->toArray());
    }

    public function getHitPoints(): int
    {
        return (int) array_sum($this->players->map(function (Player $player) {
            return $player->getHitPoints();
        })->toArray());
    }
}
