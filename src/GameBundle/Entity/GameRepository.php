<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\GameBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class GameRepository extends EntityRepository
{
    public function isArenaOpen($arena)
    {
        return !$this->getActiveGame($arena);
    }

    public function getActiveGame($arena)
    {
        $criteria = new Criteria();

        $criteria->where($criteria->expr()->eq('arena', $arena));
        $criteria->andWhere($criteria->expr()->gte('endsAt', new \DateTime()));

        return $this->matching($criteria)->first();
    }


}
