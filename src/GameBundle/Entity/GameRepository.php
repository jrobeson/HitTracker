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

namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use LazerBall\HitTracker\Model\Game;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class GameRepository extends EntityRepository
{
    public function isArenaOpen(int $arena): bool
    {
        return !$this->getActiveGame($arena);
    }

    public function getRecentGames(int $howMany, int $arena = null): ?array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('g')
            ->from('HitTracker:Game', 'g')
            ->orderBy('g.createdAt', 'DESC')
            ->setMaxResults($howMany);
        if ($arena) {
            $qb->where('g.arena = :arena');
            $qb->setParameter('arena', $arena);
        }

        return $qb->getQuery()->getResult();
    }

    public function getMostRecentGame(int $arena): ?Game
    {
        $game = $this->getRecentGames(1, $arena);

        return $game ? $game[0] : null;
    }

    public function getActiveGame(int $arena): ?Game
    {
        $criteria = new Criteria();

        $criteria->where($criteria->expr()->eq('arena', $arena));
        $criteria->andWhere($criteria->expr()->gte('endsAt', new \DateTime()));

        return $this->matching($criteria)->first() ?: null;
    }

    public function getActiveGames(): ? array
    {
        $criteria = new Criteria();

        $criteria->andWhere($criteria->expr()->gte('endsAt', new \DateTime()));

        return $this->matching($criteria)->toArray();
    }
}
