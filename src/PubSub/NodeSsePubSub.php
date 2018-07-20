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

namespace App\PubSub;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class NodeSsePubSub implements PubSubInterface
{
    private $url;
    private $httpClient;
    private $requestStack;

    public function __construct(string $url, RequestStack $requestStack)
    {
        $this->url = $url;
        $this->requestStack = $requestStack;
        $this->httpClient = new Client();
    }

    /**
     * @param mixed[] $data
     */
    public function publish(string $event, array $data): bool
    {
        $schemeAndHost = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
        $this->httpClient->post($schemeAndHost.$this->url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'event' => $event,
                'data' => $data,
            ],
        ]);

        return true;
    }
}
