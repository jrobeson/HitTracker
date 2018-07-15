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

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PlayerData
{
    /**
     * @var string
     * @Assert\NotBlank
     */
    public $team = '';

    /**
     * @var string
     * @Assert\NotBlank
     */
    public $name = '';

    /**
     * @var int
     * @Assert\Type("integer")
     */
    public $hitPoints = 0;

    /**
     * @var Vest
     */
    public $unit;

    public function setUnit(Vest $unit): void
    {
        $this->unit = $unit;
    }
}
