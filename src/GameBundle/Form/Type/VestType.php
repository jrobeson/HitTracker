<?php

namespace App\GameBundle\Form\Type;

use App\Model\Vest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $zones = range(1, 6);
        $unitTypeChoices = array_combine(
            array_map('ucfirst', Vest::getUnitTypes()),
            Vest::getUnitTypes()
        );
        $colorChoices = array_combine(
            array_map('ucfirst', Vest::getColors()),
            Vest::getColors()
        );
        $illuminationStyleChoices = array_combine(
            array_map('strtoupper', Vest::getIlluminationStyles()),
            Vest::getIlluminationStyles()
        );

        $builder
            ->add('id', IntegerType::class, [
                'label' => 'hittracker.vest.id',
                'attr' => [
                    'help' => 'hittracker.vest.id.help'
                ],
            ])
            ->add('unitType', ChoiceType::class, [
                'choices' => $unitTypeChoices,
                'label' => 'hittracker.vest.unit_type',
                'attr' => [
                    'help' => 'hittracker.vest.unit_type.help'
                ],
            ])
            ->add('color', ChoiceType::class, [
                'choices' => $colorChoices,
                'label' => 'hittracker.vest.color',
                'attr' => [
                    'help' => 'hittracker.vest.color.help'
                ],
            ])
            ->add('zones', ChoiceType::class, [
                'choices' => array_combine($zones, $zones),
                'label' => 'hittracker.vest.zones',
                'attr' => [
                    'help' => 'hittracker.vest.zones.help'
                ],
            ])
            ->add('illuminationStyle', ChoiceType::class, [
                'choices' => $illuminationStyleChoices,
                'label' => 'hittracker.vest.illumination_style',
                'attr' => [
                    'help' => 'hittracker.vest.illumination_style.help'
                ],
            ])
            ->add('radioId', TextType::class, [
                'label' => 'hittracker.vest.radio_id',
                'attr' => [
                    'help' => 'hittracker.vest.radio_id.help'
                ]
            ])
            ->add('active', CheckboxType::class, [
                    'label' => 'hittracker.vest.enabled',
                    'required' => false,
                    'data' => true
                ]
            )
            ->add('save', SubmitType::class, [
                    'label' => 'hittracker.save',
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
            'data_class' => Vest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @todo rename once sylius/resource-bundle allows global configuration
     */
    public function getName(): string
    {
        return 'hittracker_vest';
    }
}
