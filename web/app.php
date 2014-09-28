<?php
/**
 * Symfony App Front Controller
 *
 * @todo consider using the ApcClassLoader
 */
use Symfony\Component\HttpFoundation\Request;

$env = 'prod';
$debug = false;

// choose environment
if ('app_dev.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
    $env = 'dev';
}


// use environment
if ('dev' == $env) {
    $debug = true;
    require_once __DIR__.'/../app/autoload.php';
    if (isset($_SERVER['HTTP_CLIENT_IP'])
        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        || !(in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1'])
        || php_sapi_name() === 'cli-server')
    ) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file.');
    }
} else {
    require_once __DIR__.'/../var/bootstrap.php.cache';
}
require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel($env, $debug);

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
