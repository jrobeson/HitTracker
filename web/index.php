<?php
/**
 * Symfony App Front Controller
 */
use function Cekurte\Environment\env;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    $dotEnv = new \Dotenv\Dotenv(__DIR__.'/../');
    $dotEnv->load();
}
$env = env('SYMFONY_ENV', 'prod');
$debug = env('SYMFONY_DEBUG', false);
$buildType = env('SYMFONY__BUILD_TYPE', 'hosted');

if ('prod' == $env) {
    require_once __DIR__.'/../var/bootstrap.php.cache';
}

$kernelClass = ucfirst($buildType).'AppKernel';
$kernel = new $kernelClass($env, $debug);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

if (function_exists('register_postsend_function')) {
    register_postsend_function(function () use ($kernel, $request, $response) {
        $kernel->terminate($request, $response);
    });
} else {
    $kernel->terminate($request, $response);
}
