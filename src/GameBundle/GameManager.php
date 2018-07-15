<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace App\GameBundle;

use Doctrine\ORM\EntityRepository;

class GameManager
{
    private $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    //public function getActiveGames()
}
