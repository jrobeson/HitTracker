<?php declare(strict_types=1);
/*
 * Symfony App Front Controller
 */
use LazerBall\HitTracker\AppCache;
use LazerBall\HitTracker\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

if (!isset($_SERVER['APP_ENV']) && file_exists(__DIR__.'/../.env')) {
    (new Dotenv())->load(__DIR__.'/../.env');
}

$envVars = Kernel::getVarsFromEnv();
$env = $envVars['APP_ENV'];
$debug = (bool) ($envVars['APP_DEBUG'] ?? ('production' !== $env));

$kernel = new Kernel($env, $debug, $envVars['APP_BUILD_TYPE']);

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
