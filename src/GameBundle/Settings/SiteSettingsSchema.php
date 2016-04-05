<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */
namespace LazerBall\HitTracker\GameBundle\Settings;

use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SiteSettingsSchema implements SchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilderInterface $builder)
    {
        $builder
            ->setDefaults([
                'arenas' => 1,
                'business_name' => 'Your LazerBall Arena',
                'business_address' => "123 Anywhere St\nSuite 206",
                'business_phone' => '123-555-1234',
                'business_email' => 'lazerball@example.com',
                'scoreboard_logo' => 'uploads/scoreboard_logo.jpg',
                'scoreboard_banner_1' => 'uploads/scoreboard_banner_1.jpg',
                'scoreboard_banner_2' => 'uploads/scoreboard_banner_2.jpg',
            ])
            ->setAllowedTypes('arenas', ['int'])
            ->setAllowedTypes('business_name', ['string'])
            ->setAllowedTypes('business_address', ['string'])
            ->setAllowedTypes('business_phone', ['string'])
            ->setAllowedTypes('business_email', ['string'])
            ->setAllowedTypes('scoreboard_logo', ['string'])
            ->setAllowedTypes('scoreboard_banner_1', ['string'])
            ->setAllowedTypes('scoreboard_banner_2', ['string'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('arenas', 'integer', [
                'label' => 'hittracker.settings.site.arenas',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.site.arenas.help',
                ],
            ])
            ->add('business_name', 'text', [
                'label' => 'hittracker.settings.site.business_name',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_name.help',
                ],
            ])
            ->add('business_address', 'textarea', [
                'label' => 'hittracker.settings.site.business_address',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_address.help',
                ],
            ])
            ->add('business_phone', 'text', [
                'label' => 'hittracker.settings.site.business_phone',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_phone.help',
                ],
            ])
            ->add('business_email', 'text', [
                'label' => 'hittracker.settings.site.business_email',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_email.help',
                ],
            ])
            ->add('scoreboard_logo', 'text', [
                'label' => 'hittracker.settings.site.scoreboard_logo',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.scoreboard_logo.help',
                ],
            ])
            ->add('scoreboard_banner_1', 'text', [
                'label' => 'hittracker.settings.site.scoreboard_banner_1',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.scoreboard_banner_1.help',
                ],
            ])
            ->add('scoreboard_banner_2', 'text', [
                'label' => 'hittracker.settings.site.scoreboard_banner_2',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.scoreboard_banner_2.help',
                ],
            ])
        ;
    }
}
