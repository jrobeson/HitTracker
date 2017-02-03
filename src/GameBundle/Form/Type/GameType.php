<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use LazerBall\HitTracker\Model\Game;
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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    private $settingsManager;
    private $eventSubscriber;

    /**
     * @param SettingsManagerInterface $settingsManager
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function __construct(/*$settings*/SettingsManagerInterface $settingsManager, EventSubscriberInterface $eventSubscriber)
    {
        $this->settingsManager = $settingsManager;
        $this->eventSubscriber = $eventSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameSettings = $this->settingsManager->load('game');
        $siteSettings = $this->settingsManager->load('site');

        $arenas = $siteSettings->get('arenas');
        $arenaFieldType = ($arenas > 1) ? ChoiceType::class : HiddenType::class;

        $builder
            ->add('game_length', IntegerType::class, [
                'label' => 'hittracker.game.length_in_minutes',
                'data' => $gameSettings->get('game_length'),
            ])
            ->add('game_type', ChoiceType::class, [
                'choices' => array_combine(Game::getHumanGameTypes(), Game::getGameTypes()),
                'label' => 'hittracker.game.type',
                'data' => $gameSettings->get('game_type'),
                'property_path' => 'gameType'
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
            ->add('playerScorePerHit', IntegerType::class, [
                'label' => 'hittracker.game.player_score_per_hit',
                'data' => $gameSettings->get('player_score_per_hit'),
            ])
            ->add('reload_players', ListGamesType::class, [
                'label' => 'hittracker.game.load_players_from_previous_games',
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
        foreach (range(1, $gameSettings->get('team_count')) as $teamNo) {
            $builder->add('team' . $teamNo, TextType::class, [
                'label' => '',
                'mapped' => false,
                'data' => 'Team ' . $teamNo,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
            'empty_data' => function (FormInterface $form) {

            },
        ]);
    }

    public function getName()
    {
        return 'hittracker_game';
    }
}
