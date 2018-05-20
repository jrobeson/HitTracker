<?php declare(strict_types=1);
/*
 * Symfony App Front Controller
 */
use LazerBall\HitTracker\AppCache;
use LazerBall\HitTracker\Kernel;
use Symfony\Component\HttpFoundation\Request;
use function Cekurte\Environment\env;

require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    $dotEnv = new \Dotenv\Dotenv(__DIR__.'/../');
    $dotEnv->load();
}
$env = env('APP_ENV', 'production');
$debug = (bool) env('APP_DEBUG', false);
$buildType = env('HITTRACKER_BUILD_TYPE');

$kernel = new Kernel($env, $debug, $buildType);

if ('development' !== $env) {
    $kernel = new AppCache($kernel);
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();

// @todo: remove this when we have other exception handling than html
if (!in_array($request->getContentType(), ['text/html', ''])) {
    ini_set('html_errors', 'Off');
}

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
