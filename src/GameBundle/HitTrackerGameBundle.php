<?php

namespace LazerBall\HitTracker\GameBundle;

use LazerBall\HitTracker\GameBundle\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HitTrackerGameBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new Extension();
    }
}
