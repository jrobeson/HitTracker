<?php declare(strict_types=1);
/**
 * Copyright (C) 2017 Johnny Robeson <johnny@localmomentum.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\PubSubBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class Extension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        $handlers = $mergedConfig['handlers'] ?? [];
        $this->registerPubSubServices($container, $handlers);
    }

    /**
     * @param mixed[] $handlers
     */
    private function registerPubSubServices(ContainerBuilder $container, array $handlers): void
    {
        $handlerDefinition = $container->findDefinition('hittracker_pubsub.handler');

        $nodeSsePubSubConfig = $handlers['node_sse_pubsub'] ?? [];
        if (!empty($nodeSsePubSubConfig['publish_url'])) {
            $definition = $container->findDefinition('hittracker_pubsub.handler.node_sse_pubsub');
            $handlerDefinition->setClass((string) $definition->getClass());
            $handlerDefinition->addArgument($nodeSsePubSubConfig['publish_url']);
            $handlerDefinition->addArgument(new Reference('request_stack'));
        }
    }

    public function getAlias(): string
    {
        return 'hittracker_pubsub';
    }
}
