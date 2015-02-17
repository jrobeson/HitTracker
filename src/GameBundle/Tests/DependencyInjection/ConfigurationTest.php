<?php

namespace HitTracker\GameBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;
use HitTracker\GameBundle\DependencyInjection\Configuration;

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
