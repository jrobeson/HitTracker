<?php

namespace LazerBall\HitTracker\GameBundle\Tests\DependencyInjection;

use LazerBall\HitTracker\GameBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;

class ConfigurationTest extends AbstractConfigurationTestCase
{
    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testProcessedValueContainsRequiredValue()
    {
        $this->assertProcessedConfigurationEquals([
            [
                'event_handlers' => [
                    'nginx_push_stream' => [
                        'url' => 'http://example.org/publish/game'
                    ]
                ]
           ]
        ], [
                'event_handlers' => [
                    'nginx_push_stream' => [
                        'url' => 'http://example.org/publish/game'
                    ]
                ]
            ]
        );
    }
}
