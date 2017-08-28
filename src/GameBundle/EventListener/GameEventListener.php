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

namespace LazerBall\HitTracker\GameBundle\EventListener;

use GuzzleHttp\Client;
use LazerBall\HitTracker\PubSub\PubSubInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

class GameEventListener
{
    private $pubSubClient;

    public function __construct(PubSubInterface $pubSubClient)
    {
        $this->pubSubClient = $pubSubClient;
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

        $url = 'http://localhost:3000/start';
        $httpClient = new Client();

        $radioIds = [];
        foreach ($resource->getPlayers() as $player) {
            $radioIds[] = $player->getUnit()->getRadioId();
        }
        $gameConfiguration = [
            'radioIds' => $radioIds,
            'hitUrl' => 'http://localhost/games/hit',
            'gameLength' => $data['ends_at'] - $data['created_at']
        ];
        $httpClient->post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $gameConfiguration,
        ]);
    }
}
