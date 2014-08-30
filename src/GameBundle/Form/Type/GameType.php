<?php

namespace HitTracker\GameBundle\Form\Type;

use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameType extends AbstractType
{
    private $settingsManager;

    /**
     * @param SettingsManagerInterface $settingsManager
     */
    public function __construct(SettingsManagerInterface $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameSettings = $this->settingsManager->loadSettings('game');

        $arenas = $gameSettings->get('arenas');
        $arenaFieldType = ($arenas > 1) ? 'choice' : 'hidden';

        $builder
            ->add('game_length', 'hittracker_common_positive_integer', [
                'label' => 'Game Length (in minutes)',
                'data' => $gameSettings->get('game_length'),
            ])
            ->add('arena', $arenaFieldType, [
                'data' => 1
            ])
            ->add('playerLifeCredits', 'hittracker_common_positive_integer', [
                'label' => 'Life Credits Per Player',
                'data' => $gameSettings->get('player_life_credits'),
                'attr' => [
                    'step' => $gameSettings->get('life_credits_deducted')
                 ]
            ])
            ->add('lifeCreditsDeducted', 'hittracker_common_positive_integer', [
                'label' => 'Credits Deducted Per Hit',
                'data' => $gameSettings->get('life_credits_deducted'),
            ])
            ->add('players', 'hittracker_player_collection')
            ->add('start', 'submit', [
                'label' => 'Start Game',
            ])
            ->add('reset', 'reset')
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $game = $event->getData();

                foreach ($game['players'] as $k => $v) {
                    if (empty($v['lifeCredits'])) {
                        $game['players'][$k]['lifeCredits'] = $game['playerLifeCredits'];
                    }
                    /*if (0 == $player['name']) {
                       $game['players']['lifeCredits'] = '';
                    }*/
                    //$game['players'][$k]['lifeCredits'] = 500;
                    if (empty($v['name'])) {
                        unset($game['players'][$k]);
                    }
                }
                $event->setData($game);
            });

        /*$builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $game = $event->getData();

                foreach ($game->getPlayers() as $player) {
                    if (empty($player->getLifeCredits())) {
                        $player->setLifeCredits($lifeCredits);
                    }
                    //$game['players'][$k]['lifeCredits'] = 500;
                    /*if (empty($v['name'])) {
                        $game['players'][$k]['team'] = '';
                        $game['players'][$k]['vest'] = '';
                        $game['players'][$k]['life_credits'] = '';
                    }*/
                /*}
                $event->setData($game);
            });*/

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HitTracker\GameBundle\Entity\Game',
            'error_mapping' => [
                '.' => 'city',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hittracker_game';
    }
}
