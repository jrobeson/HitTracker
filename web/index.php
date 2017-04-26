<?php declare(strict_types=1);
/*
 * Symfony App Front Controller
 */
use LazerBall\HitTracker\{ElectronAppKernel, HostedAppKernel, StandaloneAppKernel};
use Symfony\Component\HttpFoundation\Request;
use function Cekurte\Environment\env;

require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    $dotEnv = new \Dotenv\Dotenv(__DIR__.'/../');
    $dotEnv->load();
}
$env = env('SYMFONY_ENV', 'production');
$debug = env('SYMFONY_DEBUG', false);
$buildType = env('HITTRACKER_BUILD_TYPE');

switch ($buildType) {
   case 'electron':
        $kernel = new ElectronAppKernel($env, $debug);
        break;
   case 'hosted':
        $kernel = new HostedAppKernel($env, $debug);
        break;
   case 'standalone':
        $kernel = new StandaloneAppKernel($env, $debug);
        break;
}

$request = Request::createFromGlobals();

// @todo: remove this when we have other exception handling than html
if (!in_array($request->getContentType(), ['text/html', ''])) {
    ini_set('html_errors', 'Off');
}

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
