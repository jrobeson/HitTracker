<?php declare(strict_types=1);

namespace LazerBall\HitTracker;

class HostedAppKernel extends AppKernel
{
    public function registerBundles(): array
    {
        $bundles = [
            new \LazerBall\HitTracker\PdoSessionHandlerBundle\PdoSessionHandlerBundle(),
        ];

        return array_merge(parent::registerBundles(), $bundles);
    }

    protected function getBuildType(): string
    {
        return 'hosted';
    }
}
