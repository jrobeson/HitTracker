<?php
/**
 * Symfony App Front Controller
 *
 * @todo consider using the ApcClassLoader
 */
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../app/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    $dotEnv = new \Dotenv\Dotenv();
    $dotEnv->load(__DIR__.'/../');
}
$env = $_SERVER['SYMFONY_ENV'];
$debug = (bool)$_SERVER['SYMFONY_DEBUG'];
$buildType = $_SERVER['SYMFONY__BUILD_TYPE'];

if ('prod' == $env) {
    require_once __DIR__.'/../var/bootstrap.php.cache';
}
require_once __DIR__.'/../app/AppKernel.php';

$kernel = AppKernelFactory::get($buildType, $env, $debug);

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
