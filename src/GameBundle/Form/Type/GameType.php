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

use LazerBall\HitTracker\Model\Game;
use LazerBall\HitTracker\Model\NewGameData;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    private $settingsManager;
    private $eventSubscriber;

    public function __construct(SettingsManagerInterface $settingsManager, EventSubscriberInterface $eventSubscriber)
    {
        $this->settingsManager = $settingsManager;
        $this->eventSubscriber = $eventSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $globalMatchSettings = $this->settingsManager->load('game');
        $siteSettings = $this->settingsManager->load('site');

        $arenas = $siteSettings->get('arenas');
        $arenaFieldType = ($arenas > 1) ? ChoiceType::class : HiddenType::class;

        $builder
            ->add('game_length', IntegerType::class, [
                'label' => 'hittracker.game.length_in_minutes',
                'data' => $globalMatchSettings->get('game_length'),
                'property_path' => 'gameLength',
            ])
            ->add('game_type', ChoiceType::class, [
                'choices' => array_combine(Game::getHumanGameTypes(), Game::getGameTypes()),
                'label' => 'hittracker.game.type',
                'data' => $globalMatchSettings->get('game_type'),
                'property_path' => 'gameType'
            ])
            ->add('arena', $arenaFieldType, [
                'data' => 1,
            ])
            ->add('settings', MatchSettingsType::class, [
                'label' => 'Settings',
            ])
            ->add('reload_players', ListGamesType::class, [
                'label' => 'hittracker.game.load_players_from_previous_games',
                'mapped' => false,
                'placeholder' => 'hittracker.game.choose',
                'required' => false,
            ])
            ->add('start', SubmitType::class, [
                'label' => 'hittracker.game.start',
            ])
            ->add('reset', ResetType::class, [
                'label' => 'hittracker.reset',
            ])
            ->addEventSubscriber($this->eventSubscriber)
        ;
        foreach (range(1, $globalMatchSettings->get('team_count')) as $teamNo) {
            $teamColor = 'green';
            if (2 === $teamNo) {
                $teamColor = 'orange';
            }
            $builder->add('team'.$teamNo, TeamPlayersType::class, [
                'label' => false,
                'teamNo' => $teamNo,
                'teamColor' => $teamColor,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewGameData::class,
            'empty_data' => function (FormInterface $form) {
            },
        ]);
    }

    public function getName(): string
    {
        return 'hittracker_game';
    }
}
