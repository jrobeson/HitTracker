<?php

namespace HitTracker\GameBundle;

use HitTracker\GameBundle\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HitTrackerGameBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new Extension();
    }
}
