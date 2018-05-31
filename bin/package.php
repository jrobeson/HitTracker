#!/usr/bin/env php
<?php

use LazerBall\HitTracker\Command\PackageCommand;
use Symfony\Component\Console\Application;

require __DIR__.'/../vendor/autoload.php';

ini_set('memory_limit', -1);
$application = new Application();

$packageCommand = new PackageCommand();
$application->add($packageCommand);
$application->setDefaultCommand($packageCommand->getName(), true);

$application->run();
