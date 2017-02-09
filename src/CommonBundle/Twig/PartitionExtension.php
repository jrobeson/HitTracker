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

namespace LazerBall\HitTracker\CommonBundle\Twig;

class PartitionExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'partition';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('partition', [$this, 'partition']),
        ];
    }

    public function partition($items, $size = 2)
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items, false);
        }

        $listSize = count($items);
        $partSize = floor($listSize / $size);

        $partLeft = $listSize % $size;
        $partition = [];
        $mark = 0;
        for($a = 0; $a < $size; $a++) {
            $incr = ($a < $partLeft) ? $partSize + 1 : $partSize;
            $partition[$a] = array_slice($items, (int) $mark, $incr);
            $mark += $incr;
        }

        return $partition;
    }
}
