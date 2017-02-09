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

use Symfony\Component\Validator\Constraints as Assert;

class GameSettings
{
    /**
     * @var int
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_enough_hit_points"
     * )
     */
    private $playerHitPoints;

    /**
     * @var int
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.not_enough_deducted_hit_points"
     * )
     */
    private $playerHitPointsDeducted;

    /**
     * @var int
     * @Assert\Type("integer")
     * @Assert\GreaterThan(
     *      value=0,
     *      message="hittracker.game.score.must_not_be_empty"
     * )
     */
    private $playerScorePerHit;

    public function __construct()
    {
        $this->playerHitPoints = 0;
        $this->playerHitPointsDeducted = 0;
        $this->playerScorePerHit = 0;
    }

    public function setPlayerHitPoints(int $playerHitPoints)
    {
        $this->playerHitPoints = $playerHitPoints;
    }

    public function getPlayerHitPoints(): int
    {
        return $this->playerHitPoints;
    }

    public function setPlayerHitPointsDeducted(int $playerHitPointsDeducted)
    {
        $this->playerHitPointsDeducted = $playerHitPointsDeducted;
    }

    public function getPlayerHitPointsDeducted(): int
    {
        return $this->playerHitPointsDeducted;
    }

    public function setPlayerScorePerHit(int $playerScorePerHit)
    {
        $this->playerScorePerHit = $playerScorePerHit;
    }

    public function getPlayerScorePerHit(): int
    {
        return $this->playerScorePerHit;
    }
}
