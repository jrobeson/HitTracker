<?php

namespace HitTracker\GameBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('name', 'text')
            ->add('team', 'hidden')
            ->add('vest', 'entity', [
                  'class' => 'HitTracker\GameBundle\Entity\Vest',
                  'choices' => $this->vestRepository->findActiveVests(),
                  'property' => 'id',
            ])
            ->add('lifeCredits', 'positive_integer', [
                  'empty_data' => '',
                  'attr' => [
                    'step' => $gameSettings->get('life_credits_deducted')
                  ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HitTracker\GameBundle\Entity\Player',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hittracker_player';
    }
}
