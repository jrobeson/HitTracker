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
            ->add('game_length', 'integer', [
                'label' => 'hittracker.game.length_in_minutes',
                'data' => $gameSettings->get('game_length'),
            ])
            ->add('arena', $arenaFieldType, [
                'data' => 1,
            ])
            ->add('playerHitPoints', 'integer', [
                'label' => 'hittracker.game.hit_points_per_player',
                'data' => $gameSettings->get('player_hit_points'),
                'attr' => [
                    'step' => $gameSettings->get('player_hit_points_deducted'),
                 ],
            ])
            ->add('playerHitPointsDeducted', 'integer', [
                'label' => 'hittracker.game.hit_points_deducted_per_hit',
                'data' => $gameSettings->get('player_hit_points_deducted'),
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
                'label' => 'hittracker.game.load_players_from_previous_games',
                'choices' => $gameList,
                'mapped' => false,
                'placeholder' => 'hittracker.game.choose',
                'required' => false,
            ])
            ->add('players', 'hittracker_player_collection')
            ->add('start', 'submit', [
                'label' => 'hittracker.game.start',
            ])
            ->add('reset', 'reset', [
                'label' => 'hittracker.reset',
            ])
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
