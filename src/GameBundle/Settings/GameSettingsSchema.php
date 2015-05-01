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
                'label' => 'Game Length',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
            ->add('player_count', 'integer', [
                'label' => 'Players Per Game',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'help' => 'This setting still requires you to have enough active vests.'
                    ]
            ])
            ->add('team_player_count', 'integer', [
                'label' => 'Players Per Team',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'help' => 'This setting still requires you to have enough players.'
                    ]
            ])
            ->add('player_life_credits', 'integer', [
                'label' => 'Player Life Credits',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
            ->add('life_credits_deducted', 'integer', [
                'label' => 'Life Credits Deducted Per Hit',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
            ])
        ;
    }
}
