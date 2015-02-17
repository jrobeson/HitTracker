<?php

namespace HitTracker\GameBundle\DependencyInjection;

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
        foreach ($this->flattenKeys($mergedConfig, 'hittracker_game.') as $k => $v) {
            $container->setParameter($k, $v);
        }

        $loader = new Loader\XmlFileLoader($container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
    }

    private function flattenKeys(array $array, $prefix = '') {
        $result = array();
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $result = $result + $this->flattenKeys($value, $prefix.$key.'.');
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'hittracker_game';
    }
}
