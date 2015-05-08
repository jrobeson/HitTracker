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
                'player_life_credits' => 500,
                'life_credits_deducted' => 10,
            ])
            ->setAllowedTypes([
                'game_length'  => ['int'],
                'player_count' => ['int'],
                'team_player_count' => ['int'],
                'player_life_credits' => ['int'],
                'life_credits_deducted' => ['int'],
            ]);
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
                    'help' => 'hittracker.settings.game.help.players_per_game'
                    ]
            ])
            ->add('team_player_count', 'integer', [
                'label' => 'hittracker.settings.game.players_per_team',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'help' => 'hittracker.settings.game.help.players_per_team'
                    ]
            ])
            ->add('player_life_credits', 'integer', [
                'label' => 'hittracker.game.life_credits_per_player',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
            ->add('life_credits_deducted', 'integer', [
                'label' => 'hittracker.game.live_credits_deducted_per_hit',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
        ;
    }
}
