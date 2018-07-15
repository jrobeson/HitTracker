<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace App\GameBundle\EventListener;

use App\Repository\GameRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GameFormSubscriber implements EventSubscriberInterface
{
    private $repository;

    public function __construct(GameRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Check if the game arena is open for playing
     *
     * @todo translations
     */
    public function arenaOpenCheck(FormEvent $event): bool
    {
        $arena = $event->getData()->getArena();

        if ($this->repository->isArenaOpen($arena)) {
            return true;
        }

        $event->getForm()->addError(
            new FormError('A game is already in progress in arena '.$arena)
        );

        return false;
    }

    /**
     * Remove players that that were filled out
     */
    public function removeUnusedPlayers(FormEvent $event): void
    {
        $game = $event->getData();
        foreach (range(1, 2) as $team) {
            $game['team'.$team]['players'] = array_filter($game['team'.$team]['players'],
                function ($player) {
                    return !empty($player['name']);
                }
            );
        }
        $event->setData($game);
    }

    /**
     * Use the game default hit points if none were
     * specified for the player
     */
    public function addHitPoints(FormEvent $event): void
    {
        $game = $event->getData();

        foreach (range(1, 2) as $team) {
            foreach ($game['team'.$team]['players'] as $k => $v) {
                if (empty($v['hitPoints'])) {
                    $game['team'.$team]['players'][$k]['hitPoints'] = $game['settings']['playerHitPoints'];
                }
            }
        }
        $event->setData($game);
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => [
                ['removeUnusedPlayers'],
                ['addHitPoints'],
            ],
            FOrmEvents::POST_SUBMIT => ['arenaOpenCheck'],
        ];
    }
}
