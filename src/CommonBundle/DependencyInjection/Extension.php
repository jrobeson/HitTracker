<?php

namespace LazerBall\HitTracker\CommonBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;

class Extension extends BaseExtension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
    }

    public function getAlias()
    {
        return 'hittracker_common';
    }
}
