<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */
namespace LazerBall\HitTracker\GameBundle\Settings;

use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ])
            ->setAllowedTypes('game_length', ['int'])
            ->setAllowedTypes('player_count', ['int'])
            ->setAllowedTypes('team_player_count', ['int'])
            ->setAllowedTypes('player_hit_points', ['int'])
            ->setAllowedTypes('player_hit_points_deducted', ['int'])
        ;
    }

    /**
     * {@inheritdoc}
     * @todo players per game should check for enough vests
     * @todo players per team should check for enough players
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('game_length', IntegerType::class, [
                'label' => 'hittracker.settings.game.length',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.length.help'
                ],
            ])
            ->add('player_count', IntegerType::class, [
                'label' => 'hittracker.settings.game.players_per_game',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.players_per_game.help'
                 ],
            ])
            ->add('team_player_count', IntegerType::class, [
                'label' => 'hittracker.settings.game.players_per_team',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.players_per_team.help'
                ],
            ])
            ->add('player_hit_points', IntegerType::class, [
                'label' => 'hittracker.settings.game.hit_points_per_player',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.hit_points_per_player.help'
                ],
            ])
            ->add('player_hit_points_deducted', IntegerType::class, [
                'label' => 'hittracker.settings.game.hit_points_deducted_per_hit',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.game.hit_points_deducted_per_hit.help'
                ],
            ])
        ;
    }
}
