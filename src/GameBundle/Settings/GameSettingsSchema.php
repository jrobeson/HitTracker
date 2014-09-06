<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace HitTracker\GameBundle\Settings;

use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;

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
            ->add('game_length', 'hittracker_common_positive_integer', [
                'label' => 'Game Length'
            ])
            ->add('player_count', 'hittracker_common_positive_integer', [
                'label' => 'Players Per Game',
                'attr' => [
                    'help' => 'This setting still requires you to have enough active vests.'
                    ]
            ])
            ->add('team_player_count', 'hittracker_common_positive_integer', [
                'label' => 'Players Per Team',
                'attr' => [
                    'help' => 'This setting still requires you to have enough players.'
                    ]
            ])
            ->add('player_life_credits', 'hittracker_common_positive_integer', [
                'label' => 'Player Life Credits'
            ])
            ->add('life_credits_deducted', 'hittracker_common_positive_integer', [
                'label' => 'Life Credits Deducted Per Hit'
            ])
        ;
    }
}
