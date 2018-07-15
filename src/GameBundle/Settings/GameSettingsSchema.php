<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */

namespace App\GameBundle\Settings;

use App\Form\Type\GenericFileType;
use App\Model\Game;
use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GameSettingsSchema implements SchemaInterface
{
    public function buildSettings(SettingsBuilderInterface $builder)
    {
        $builder
            ->setDefaults([
                'game_length' => 10,
                'game_type' => 'team',
                'player_count' => 20,
                'team_count' => 2,
                'team_player_count' => 10,
                'player_hit_points' => 500,
                'player_hit_points_deducted' => 10,
                'player_score_per_hit' => 10,
                'active_game_music_file' => '',
                'active_game_music_play' => true,
            ])
            ->setAllowedTypes('game_length', ['int'])
            ->setAllowedTypes('game_type', ['string'])
            ->setAllowedTypes('player_count', ['int'])
            ->setAllowedTypes('team_count', ['int'])
            ->setAllowedTypes('team_player_count', ['int'])
            ->setAllowedTypes('player_hit_points', ['int'])
            ->setAllowedTypes('player_hit_points_deducted', ['int'])
            ->setAllowedTypes('player_score_per_hit', ['int'])
            ->setAllowedTypes('active_game_music_file', ['string', 'null'])
            ->setAllowedTypes('active_game_music_play', ['bool'])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @todo players per game should check for enough vests
     * @todo players per team should check for enough players
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('game_length', IntegerType::class, [
                'label' => 'hittracker.settings.game.length',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.length.help',
            ])
            ->add('game_type', ChoiceType::class, [
                'choices' => array_combine(Game::getHumanGameTypes(), Game::getGameTypes()),
                'label' => 'hittracker.settings.game.type',
                'help' => 'hittracker.settings.game.type.help',
            ])
            ->add('player_count', IntegerType::class, [
                'label' => 'hittracker.settings.game.players_per_game',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.players_per_game.help',
            ])
            ->add('team_count', IntegerType::class, [
                'label' => 'hittracker.settings.game.team_count',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.team_count.help',
            ])
            ->add('team_player_count', IntegerType::class, [
                'label' => 'hittracker.settings.game.players_per_team',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.players_per_team.help',
            ])
            ->add('player_hit_points', IntegerType::class, [
                'label' => 'hittracker.settings.game.hit_points_per_player',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.hit_points_per_player.help',
            ])
            ->add('player_hit_points_deducted', IntegerType::class, [
                'label' => 'hittracker.settings.game.hit_points_deducted_per_hit',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.hit_points_deducted_per_hit.help',
            ])
            ->add('player_score_per_hit', IntegerType::class, [
                'label' => 'hittracker.settings.game.player_score_per_hit',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'help' => 'hittracker.settings.game.player_score_per_hit.help',
            ])
            ->add('active_game_music_file', GenericFileType::class, [
                'upload_uri_prefix' => '/music',
                'upload_use_provided_file_name' => true,
                'required' => false,
                'label' => 'hittracker.settings.game.active_music_file',
                'help' => 'hittracker.settings.game.active_music_file.help',
            ])
            ->add('active_game_music_play', CheckboxType::class, [
                'required' => false,
                'label' => 'hittracker.settings.game.active_music_play',
                'help' => 'hittracker.settings.game.active_music_play.help',
            ])
        ;
    }
}
