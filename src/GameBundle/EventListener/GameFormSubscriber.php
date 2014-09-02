<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\GameBundle\EventListener;

use HitTracker\GameBundle\Entity\GameRepository;
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
     * @param FormEvent $event
     * @todo translations
     */
    public function arenaOpenCheck(FormEvent $event)
    {
        $arena = $event->getData()->getArena();

        if ($this->repository->isArenaOpen($arena)) return true;

        $event->getForm()->addError(
            new FormError('A game is already in progress in arena '. $arena)
        );
    }

    public function removeUnusedPlayers(FormEvent $event)
    {
        $game = $event->getData();
        foreach ($game['players'] as $k => $v) {
            if (empty($v['name'])) {
                unset($game['players'][$k]);
            }
        }
        $event->setData($game);
    }
    public function addLifeCredits(FormEvent $event)
    {
        $game = $event->getData();

        foreach ($game['players'] as $k => $v) {
            if (empty($v['lifeCredits'])) {
                $game['players'][$k]['lifeCredits'] = $game['playerLifeCredits'];
            }
        }
        $event->setData($game);
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => [
                ['removeUnusedPlayers'],
                ['addLifeCredits']
            ],
            FOrmEvents::POST_SUBMIT => ['arenaOpenCheck']
        ];
    }
}
