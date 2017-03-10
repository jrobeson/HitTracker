<?php
/**
 * Symfony App Front Controller
 */
use Symfony\Component\HttpFoundation\Request;
use function Cekurte\Environment\env;

require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    $dotEnv = new \Dotenv\Dotenv(__DIR__.'/../');
    $dotEnv->load();
}
$env = env('SYMFONY_ENV', 'prod');
$debug = env('SYMFONY_DEBUG', false);
$buildType = env('SYMFONY__BUILD_TYPE', 'hosted');

$kernelClass = ucfirst($buildType).'AppKernel';
$kernel = new $kernelClass($env, $debug);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
