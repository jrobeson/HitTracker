<?php

namespace LazerBall\HitTracker\GameBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * {@inheritdoc}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hittracker_game');
        $rootNode
            ->children()
                ->arrayNode('event_handlers')
                    ->children()
                        ->arrayNode('nginx_push_stream')
                            ->children()
                                ->scalarNode('url')->defaultValue('http://localhost/publish/game')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
