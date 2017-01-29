<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */
namespace LazerBall\HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class GameRepository extends EntityRepository
{
    /**
     * @param int $arena
     * @return bool
     */
    public function isArenaOpen($arena)
    {
        return !$this->getActiveGame($arena);
    }

    /**
     * @param int $howMany
     * @param int|null $arena
     * @return array
     */
    public function getRecentGames($howMany, $arena = null)
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

    /**
     * @param int $howMany
     * @return array|null
     */
    public function getMostRecentGame($arena)
    {
        $game = $this->getRecentGames(1, $arena);

        return $game ? $game[0] : null;
    }

    /**
     * @param int $arena
     * @return Game|null
     */
    public function getActiveGame($arena)
    {
        $criteria = new Criteria();

        $criteria->where($criteria->expr()->eq('arena', $arena));
        $criteria->andWhere($criteria->expr()->gte('endsAt', new \DateTime()));

        return $this->matching($criteria)->first();
    }

    /** @return Game|null */
    public function getActiveGames()
    {
        $criteria = new Criteria();

        $criteria->andWhere($criteria->expr()->gte('endsAt', new \DateTime()));

        return $this->matching($criteria)->toArray();
    }
}
