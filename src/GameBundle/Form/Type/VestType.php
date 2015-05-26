<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('radioId', 'text', [
                'label' => 'hittracker.vest.radio_id',
                'attr' => [
                    'help' => 'hittracker.vest.radio_id.help'
                ]
            ])
            ->add('active', 'checkbox', [
                    'label' => 'hittracker.vest.enabled',
                    'required' => false,
                    'data' => true
                ]
            )
            ->add('save', 'submit', [
                    'label'  => 'hittracker.save',
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LazerBall\HitTracker\GameBundle\Entity\Vest',
        ]);
    }

    /**
     * {@inheritdoc}
     * @todo rename once sylius/resource-bundle allows global configuration
     */
    public function getName()
    {
        return 'hittracker_vest';
    }
}
