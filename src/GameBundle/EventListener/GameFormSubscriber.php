<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace LazerBall\HitTracker\GameBundle\EventListener;

use LazerBall\HitTracker\GameBundle\Entity\GameRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GameFormSubscriber implements EventSubscriberInterface
{
    private $repository;

    /**
     * @param GameRepository $repository
     */
    public function __construct(GameRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Check if the game arena is open for playing
     *
     * @param FormEvent $event
     * @todo translations
     */
    public function arenaOpenCheck(FormEvent $event)
    {
        $arena = $event->getData()->getArena();

        if ($this->repository->isArenaOpen($arena)) {
            return true;
        }

        $event->getForm()->addError(
            new FormError('A game is already in progress in arena '.$arena)
        );
    }

    /**
     * Remove players that that were filled out
     *
     * @param FormEvent $event
     */
    public function removeUnusedPlayers(FormEvent $event)
    {
        $game = $event->getData();
        $game['players'] = array_filter($game['players'],
            function ($player) {
                return !empty($player['name']);
        });

        $event->setData($game);
    }

    /**
     * Use the game default hit points if none were
     * specified for the player
     *
     * @param FormEvent $event
     */
    public function addHitPoints(FormEvent $event)
    {
        $game = $event->getData();

        foreach ($game['players'] as $k => $v) {
            if (empty($v['hitPoints'])) {
                $game['players'][$k]['hitPoints'] = $game['playerHitPoints'];
            }
        }
        $event->setData($game);
    }

    /**
     * {@inheritdoc}
     */
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
