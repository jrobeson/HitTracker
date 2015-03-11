<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('radioId', 'text', ['label' => 'Radio Id (8 character radio ID)'])
            ->add('active', 'checkbox', [
                    'label' => 'Active?',
                    'required' => false,
                    'data' => true
                ]
            )
            ->add('save', 'submit')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
