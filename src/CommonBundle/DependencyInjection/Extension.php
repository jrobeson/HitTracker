<?php

namespace LazerBall\HitTracker\CommonBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * {@inheritdoc}
 */
class Extension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container,
            new FileLocator(__DIR__.'/../Resources/config')
        )   ;
        $loader->load('services.xml');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'hittracker_common';
    }
}
