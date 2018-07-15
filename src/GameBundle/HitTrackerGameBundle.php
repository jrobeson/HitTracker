<?php

namespace App\GameBundle;

use App\GameBundle\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HitTrackerGameBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new Extension();
    }
}
