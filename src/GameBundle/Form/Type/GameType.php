<?php

namespace HitTracker\GameBundle\Form\Type;

use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameType extends AbstractType
{
    private $settingsManager;
    private $eventSubscriber;

    /**
     * @param SettingsManagerInterface $settingsManager
     * @param EventSubscriberInterface $eventSubscriber
     */
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
        $gameSettings = $this->settingsManager->loadSettings('game');
        $siteSettings = $this->settingsManager->loadSettings('siteh');

        $arenas = $siteSettings->get('arenas');
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
            ->addEventSubscriber($this->eventSubscriber)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HitTracker\GameBundle\Entity\Game',
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
