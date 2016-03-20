<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    private $settingsManager;
    private $vestRepository;

    public function __construct(SettingsManagerInterface $settingsManager, EntityRepository $vestRepository)
    {
        $this->settingsManager = $settingsManager;
        $this->vestRepository = $vestRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameSettings = $this->settingsManager->loadSettings('game');

        $builder
            ->add('name', TextType::class, [
                  'label' => 'hittracker.game.player_name'
            ])
            ->add('team', HiddenType::class)
            ->add('vest', EntityType::class, [
                  'label' => 'hittracker.game.vest',
                  'class' => 'LazerBall\HitTracker\GameBundle\Entity\Vest',
                  'choices' => $this->vestRepository->findActiveVests(),
                  'choice_label' => 'id',
            ])
            ->add('hitPoints', IntegerType::class, [
                  'empty_data' => '',
                  'label' => 'hittracker.game.hit_points',
                  'attr' => [
                    'step' => $gameSettings->get('player_hit_points_deducted'),
                    'class' => 'hidden'
                  ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LazerBall\HitTracker\GameBundle\Entity\Player',
        ]);
    }
}
