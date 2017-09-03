<?php declare(strict_types=1);
/**
 * Copyright (C) 2017 Johnny Robeson <johnny@localmomentum.net>
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

namespace LazerBall\HitTracker\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LazerBall\HitTracker\Validator\Constraints as CommonAssert;
use Symfony\Component\Validator\Constraints as Assert;

class NewGameData
{
   /**
     * @todo cap the upper bound on arenas based on how many there really are.
     *
     * @var int
     * @Assert\Type("numeric")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.arena_not_exists"
     * )
     */
    public $arena;

    public $settings;

    /**
     * @var \DateTime
     */
    public $startAt;

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
     */
    public $players;

    /**
     * @var int
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_long_enough"
     * )
     */
    public $gameLength;

    /**
     * @var string
     */
    public $gameType;

    public function __construct()
    {
        $this->players = new ArrayCollection();
    }
    public function addPlayer(PlayerData $player): void
    {
        $this->players->add($player);
    }

    public function getArena()
    {
        return $this->arena;
    }
}
