<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use LazerBall\HitTracker\GameBundle\Form\Type\PlayerCollectionType;

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
        $arenaFieldType = ($arenas > 1) ? ChoiceType::class : HiddenType::class;

        $recentGames = $this->gameRepository->getRecentGames(10, 1);
        $gameList = [];
        foreach ($recentGames as $recentGame) {
            $gameIdFormat = sprintf(' (Game #: %d)', $recentGame->getId());
            $teams = implode(' vs. ', $recentGame->getTeams()).$gameIdFormat;
            $gameList[$teams] = $recentGame->getId();
        }

        $builder
            ->add('game_length', IntegerType::class, [
                'label' => 'hittracker.game.length_in_minutes',
                'data' => $gameSettings->get('game_length'),
            ])
            ->add('arena', $arenaFieldType, [
                'data' => 1,
            ])
            ->add('playerHitPoints', IntegerType::class, [
                'label' => 'hittracker.game.hit_points_per_player',
                'data' => $gameSettings->get('player_hit_points'),
                'attr' => [
                    'step' => $gameSettings->get('player_hit_points_deducted'),
                 ],
            ])
            ->add('playerHitPointsDeducted', IntegerType::class, [
                'label' => 'hittracker.game.hit_points_deducted_per_hit',
                'data' => $gameSettings->get('player_hit_points_deducted'),
            ])
            ->add('team1', TextType::class, [
                'label' => '',
                'mapped' => false,
                'data' => 'Team 1',
            ])
            ->add('team2', TextType::class, [
                'label' => '',
                'mapped' => false,
                'data' => 'Team 2',
            ])
            ->add('reload_players', ChoiceType::class, [
                'label' => 'hittracker.game.load_players_from_previous_games',
                'choices' => $gameList,
                'choices_as_values' => true,
                'mapped' => false,
                'placeholder' => 'hittracker.game.choose',
                'required' => false,
            ])
            ->add('players', PlayerCollectionType::class)
            ->add('start', SubmitType::class, [
                'label' => 'hittracker.game.start',
            ])
            ->add('reset', ResetType::class, [
                'label' => 'hittracker.reset',
            ])
            ->addEventSubscriber($this->eventSubscriber)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LazerBall\HitTracker\GameBundle\Entity\Game',
        ]);
    }

    public function getName()
    {
        return 'hittracker_game';
    }
}
