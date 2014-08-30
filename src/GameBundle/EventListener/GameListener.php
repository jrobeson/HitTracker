<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\GameBundle\EventListener;

use HitTracker\GameBundle\Entity\GameRepository;
use Sylius\Bundle\ResourceBundle\Event\ResourceEvent;

class GameListener
{
    private $repository;

    public function __construct(GameRepository $repository)
    {
        $this->repository = $repository;
    }

    public function arenaOpenCheck(ResourceEvent $event)
    {
        $game = $event->getSubject();
        $arena = $game->getArena();
        if ($this->repository->isArenaOpen($arena)) return true;

        $event->stop('already_in_progress', 'error', ['%arena_no%' => $arena]);

    }
}
