<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class GameRepository extends EntityRepository
{
    /**
     * @param integer $arena
     * @return bool
     */
    public function isArenaOpen($arena)
    {
        return !$this->getActiveGame($arena);
    }

    /**
     * @param int $howMany
     * @return array
     */
    public function getRecentGames($howMany)
    {
        return $this->_em->createQuery('SELECT g from HitTrackerGameBundle:Game g ORDER BY g.id DESC')
            ->setMaxResults($howMany)
            ->getResult();
    }

    /**
     * @param integer $arena
     * @return Game
     */
    public function getActiveGame($arena)
    {
        $criteria = new Criteria();

        $criteria->where($criteria->expr()->eq('arena', $arena));
        $criteria->andWhere($criteria->expr()->gte('endsAt', new \DateTime()));

        return $this->matching($criteria)->first();
    }


}
