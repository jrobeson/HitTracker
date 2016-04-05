<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */
namespace LazerBall\HitTracker\GameBundle\Settings;

use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GameSettingsSchema implements SchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilderInterface $builder)
    {
        $builder
            ->setDefaults([
                'game_length'  => 10,
                'player_count' => 20,
                'team_player_count' => 10,
                'player_hit_points' => 500,
                'player_hit_points_deducted' => 10,
                'player_vest_hold_penalty' => 0,
            ])
            ->setAllowedTypes('game_length', ['int'])
            ->setAllowedTypes('player_count', ['int'])
            ->setAllowedTypes('team_player_count', ['int'])
            ->setAllowedTypes('player_hit_points', ['int'])
            ->setAllowedTypes('player_hit_points_deducted', ['int'])
            ->setAllowedTypes('player_vest_hold_penalty', ['int']);
    }

    /**
     * {@inheritdoc}
     * @todo players per game should check for enough vests
     * @todo players per team should check for enough players
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('game_length', 'integer', [
                'label' => 'hittracker.game.length',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
            ->add('player_count', 'integer', [
                'label' => 'hittracker.settings.game.players_per_game',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.players_per_game.help'
                    ]
            ])
            ->add('team_player_count', 'integer', [
                'label' => 'hittracker.settings.game.players_per_team',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.players_per_team.help'
                    ]
            ])
            ->add('player_hit_points', 'integer', [
                'label' => 'hittracker.game.hit_points_per_player',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
            ->add('player_hit_points_deducted', 'integer', [
                'label' => 'hittracker.game.hit_points_deducted_per_hit',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
            ->add('player_vest_hold_penalty', 'integer', [
                'label' => 'hittracker.game.vest_hold_penalty',
                'constraints' => [new Assert\GreaterThanOrEqual(['value' => 0])],
            ])
        ;
    }
}
