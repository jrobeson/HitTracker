<?php
class HostedAppKernel extends AppKernel
{
    protected function getBuildType()
    {
        return 'hosted';
    }
}
