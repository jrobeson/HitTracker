<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameType extends AbstractType
{
    private $settingsManager;
    private $eventSubscriber;
    private $gameRepository;

    /**
     * @param $gameRepository
     * @param SettingsManagerInterface $settingsManager
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function __construct($gameRepository, /*$settings*/SettingsManagerInterface $settingsManager, EventSubscriberInterface $eventSubscriber)
    {
        $this->settingsManager = $settingsManager;
        $this->eventSubscriber = $eventSubscriber;
        $this->gameRepository = $gameRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameSettings = $this->settingsManager->loadSettings('game');
        $siteSettings = $this->settingsManager->loadSettings('site');

        $arenas = $siteSettings->get('arenas');
        $arenaFieldType = ($arenas > 1) ? 'choice' : 'hidden';

        $recentGames = $this->gameRepository->getRecentGames(10);
        $gameList = [];
        foreach ($recentGames as $recentGame) {
            $gameIdFormat = sprintf(' (Game #: %d)', $recentGame->getId());
            $teams = implode(' vs. ', $recentGame->getTeams()).$gameIdFormat;
            $gameList[$recentGame->getId()] = $teams;
        }

        $builder
            ->add('game_length', 'positive_integer', [
                'label' => 'Game Length (in minutes)',
                'data' => $gameSettings->get('game_length'),
            ])
            ->add('arena', $arenaFieldType, [
                'data' => 1,
            ])
            ->add('playerLifeCredits', 'positive_integer', [
                'label' => 'Life Credits Per Player',
                'data' => $gameSettings->get('player_life_credits'),
                'attr' => [
                    'step' => $gameSettings->get('life_credits_deducted'),
                 ],
            ])
            ->add('lifeCreditsDeducted', 'positive_integer', [
                'label' => 'Credits Deducted Per Hit',
                'data' => $gameSettings->get('life_credits_deducted'),
            ])
            ->add('team1', 'text', [
                'label' => '',
                'mapped' => false,
                'data' => 'Team 1',
            ])
            ->add('team2', 'text', [
                'label' => '',
                'mapped' => false,
                'data' => 'Team 2',
            ])
            ->add('reload_players', 'choice', [
                'label' => 'Load Players From Previous Games',
                'choices' => $gameList,
                'mapped' => false,
            ])
            ->add('players', 'hittracker_player_collection')
            ->add('start', 'submit', [
                'label' => 'Start Game',
            ])
            ->add('reset', 'reset')
            ->addEventSubscriber($this->eventSubscriber)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LazerBall\HitTracker\GameBundle\Entity\Game',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hittracker_game';
    }
}
