<?php

namespace HitTracker\CommonBundle;

use HitTracker\CommonBundle\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CommonBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new Extension();
    }
}
