<?php
/** BoardNest landlord-only Front Controller. Other roles retain their existing entry points. */
declare(strict_types=1);
/**
 * VIVA GUIDE - FILE PURPOSE
 * Public entry point for Landlord MVC: set response security headers, load the class autoloader and application configuration, then run the router. Other role front controllers are separate.
 */

// Security policies apply only to the Landlord MVC entry point.
// Group-wide endpoints keep their original behaviour.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
// All application scripts, styles and images are hosted locally.
// This intentionally blocks inline JavaScript and third-party resources.
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'");

$moduleRoot = dirname(__DIR__, 2) . '/modules/landlord/mvc';
require $moduleRoot . '/core/Autoloader.php';
\BoardNest\Core\Autoloader::register();

$config = require $moduleRoot . '/config/config.php';
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/boardnest-merged/public/landlord/index.php'));
$config['base_path'] = rtrim($basePath, '/');
define('BOARDNEST_BASE', $config['base_path']);
require $moduleRoot . '/shared/helpers/functions.php';
(new \BoardNest\Core\App($config))->run();
