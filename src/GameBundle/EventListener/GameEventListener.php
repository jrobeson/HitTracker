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

namespace App\GameBundle\EventListener;

use App\PubSub\PubSubInterface;
use GuzzleHttp\Client;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class GameEventListener
{
    private $pubSubClient;
    protected $requestStack;

    public function __construct(PubSubInterface $pubSubClient, RequestStack $requestStack)
    {
        $this->pubSubClient = $pubSubClient;
        $this->requestStack = $requestStack;
    }

    public function onPostCreate(ResourceControllerEvent $event): void
    {
        $resource = $event->getSubject();
        $data = [
            'arena' => $resource->getArena(),
            'created_at' => $resource->getCreatedAt()->getTimestamp(),
            'ends_at' => $resource->getEndsAt()->getTimestamp(),
        ];
        $this->pubSubClient->publish('game.start', $data);

        $schemeAndHost = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $url = $schemeAndHost.'/blegateway/start';
        $httpClient = new Client();

        $units = [];
        foreach ($resource->getPlayers() as $player) {
            $unit = [];
            $thisUnit = $player->getUnit();
            $unit['radioId'] = $thisUnit->getRadioId();
            $unit['illuminationStyle'] = $thisUnit->getIlluminationStyle();
            $unit['zones'] = $thisUnit->getZones();
            $units[] = $unit;
        }
        $gameConfiguration = [
            'units' => $units,
            'hitUrl' => $schemeAndHost.'/games/hit',
            'gameLength' => $data['ends_at'] - $data['created_at']
        ];
        $httpClient->post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $gameConfiguration,
        ]);
    }
}
