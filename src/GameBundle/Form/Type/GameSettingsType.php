<?php declare(strict_types=1);
/**
 * Copyright (C) 2017 Johnny Robeson <johnny@localmomentum.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use LazerBall\HitTracker\Model\GameSettings;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameSettingsType extends AbstractType
{
    private $settingsManager;

    public function __construct(/*$settings*/SettingsManagerInterface $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameSettings = $this->settingsManager->load('game');

        $builder
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GameSettings::class,
        ]);
    }

    public function getName(): string
    {
        return 'hittracker_game_settings';
    }
}
