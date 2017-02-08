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

namespace LazerBall\HitTracker\GameBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class Extension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.xml');

        $eventHandlers = $mergedConfig['event_handlers'];
        if (array_key_exists('nginx_push_stream', $eventHandlers)) {
            $nginxPushStreamConfig = $eventHandlers['nginx_push_stream'];
            if (!empty($nginxPushStreamConfig['url']))  {
                $prefix = 'hittracker_game.event_handlers.nginx_push_stream.';
                foreach ($this->flattenKeys($nginxPushStreamConfig, $prefix) as $k => $v) {
                    $container->setParameter($k, $v);
                }
                $loader->load('pubsub_nginx_push_stream.xml');
            }
        }

    }

    private function flattenKeys(array $array, string $prefix = '') : array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->flattenKeys($value, $prefix.$key.'.');
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }

    public function getAlias()
    {
        return 'hittracker_game';
    }
}
