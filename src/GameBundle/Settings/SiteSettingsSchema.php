<?php
/**
 * @copyright 2014 Johnny Robeson <johnny@localmomentum.net>
 */
namespace LazerBall\HitTracker\GameBundle\Settings;

use LazerBall\HitTracker\CommonBundle\Form\Type\GenericFileType;
use Sylius\Bundle\SettingsBundle\Schema\SchemaInterface;
use Sylius\Bundle\SettingsBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                /*'business_facebook_account' => '',
                'business_facebook_page' => '',
                'business_twitter_account' => '',*/
                'scoreboard_logo' => 'uploads/scoreboard_logo.jpg',
                'scoreboard_banner_1' => 'uploads/scoreboard_banner_1.jpg',
                'scoreboard_banner_2' => 'uploads/scoreboard_banner_2.jpg',
            ])
            ->setAllowedTypes('arenas', ['int'])
            ->setAllowedTypes('business_name', ['string'])
            ->setAllowedTypes('business_address', ['string', 'null'])
            ->setAllowedTypes('business_phone', ['string', 'null'])
            ->setAllowedTypes('business_email', ['string', 'null'])
            /*->setAllowedTypes('business_facebook_account', ['string', 'null'])
            ->setAllowedTypes('business_facebook_page', ['string', 'null'])
            ->setAllowedTypes('business_twitter_account', ['string', 'null'])*/
            ->setAllowedTypes('scoreboard_logo', ['string', 'null'])
            ->setAllowedTypes('scoreboard_banner_1', ['string', 'null'])
            ->setAllowedTypes('scoreboard_banner_2', ['string', 'null'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $uploadUriPrefix = '/images';

        $builder
            ->add('arenas', IntegerType::class, [
                'label' => 'hittracker.settings.site.arenas',
                'constraints' => [new Assert\GreaterThan(['value' => 0])],
                'attr' => [
                    'data-help' => 'hittracker.settings.site.arenas.help',
                ],
            ])
            ->add('business_name', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Assert\NotBlank()],
                'label' => 'hittracker.settings.site.business_name',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_name.help',
                ],
            ])
            ->add('business_address', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.business_address',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_address.help',
                ],
            ])
            ->add('business_phone', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.business_phone',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_phone.help',
                ],
            ])
            ->add('business_email', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.business_email',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_email.help',
                ],
            ])
            /*->add('business_facebook_account', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.business_facebook_account',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_facebook_account.help',
                ],
            ])
            ->add('business_facebook_page', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.business_facebook_page',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_facebook_page.help',
                ],
            ])
            ->add('business_twitter_account', TextType::class, [
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.business_twitter_account',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.business_twitter_account.help',
                ],
            ])*/
            ->add('scoreboard_logo', GenericFileType::class, [
                'upload_uri_prefix' => $uploadUriPrefix,
                'upload_use_provided_file_name' => true,
                'label' => 'hittracker.settings.site.scoreboard_logo',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.scoreboard_logo.help',
                ],
               'required' => false,
            ])
            ->add('scorebard_banner_2', GenericFileType::class, [
                'upload_uri_prefix' => $uploadUriPrefix,
                'upload_use_provided_file_name' => true,
                'required' => false,
                'empty_data' => '',
                'label' => 'hittracker.settings.site.scoreboard_banner_1',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.scoreboard_banner_1.help',
                ],
            ])
            ->add('scoreboard_banner_2', GenericFIleType::class, [
                'upload_uri_prefix' => $uploadUriPrefix,
                'upload_use_provided_file_name' => true,
                'required' => false,
                'label' => 'hittracker.settings.site.scoreboard_banner_2',
                'attr' => [
                    'data-help' => 'hittracker.settings.site.scoreboard_banner_2.help',
                ],
            ])
        ;
    }
}
