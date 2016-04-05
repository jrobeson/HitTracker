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
                'business_email' => 'lazerball@example.com'
            ])
            ->setAllowedTypes('arenas', ['int'])
            ->setAllowedTypes('business_name', ['string'])
            ->setAllowedTypes('business_address', ['string'])
            ->setAllowedTypes('business_phone', ['string'])
            ->setAllowedTypes('business_email', ['string'])
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
            ])
            ->add('business_name', 'text', [
                'label' => 'hittracker.settings.site.business_name',
            ])
            ->add('business_address', 'textarea', [
                'label' => 'hittracker.settings.site.business_address',
            ])
            ->add('business_phone', 'text', [
                'label' => 'hittracker.settings.site.business_phone',
            ])
            ->add('business_email', 'text', [
                'label' => 'hittracker.settings.site.business_email',
            ])
        ;
    }
}
