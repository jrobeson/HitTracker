<?php
class StandaloneAppKernel extends AppKernel
{
    protected function getBuildType()
    {
        return 'standalone';
    }
}
