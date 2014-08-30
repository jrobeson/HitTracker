<?php

namespace HitTracker\GameBundle\Form\Type;

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
            ->add('esn', 'text', ['label' => 'ESN (8 character vest ID)'])
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
            'data_class' => 'HitTracker\GameBundle\Entity\Vest',
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
