<?php declare(strict_types=1);

class HostedAppKernel extends AppKernel
{
    public function registerBundles() : array
    {
        $bundles = [
            new LazerBall\HitTracker\PdoSessionHandlerBundle\PdoSessionHandlerBundle(),
        ];
        return array_merge(parent::registerBundles(), $bundles);
    }

    protected function getBuildType() : string
    {
        return 'hosted';
    }
}
