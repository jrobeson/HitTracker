<?php declare(strict_types=1);

/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */
namespace LazerBall\HitTracker\GameBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class VestRepository extends EntityRepository
{
    public function findActiveVests(?string $unitType = null) : array
    {
        $query = ['active' => true];
        if (!empty($unitType)) {
            $query['unitType'] = $unitType;
        }
        return $this->findBy($query, ['id' => 'ASC']);
    }
}
