<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\GameBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class VestRepository extends EntityRepository
{
    /** @return array */
    public function findActiveVests()
    {
        return $this->findBy(['active' => true], ['id' => 'ASC']);
    }
}
