<?php

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

$storagePath = sys_get_temp_dir().'/iems';

foreach (['bootstrap/cache', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    $directoryPath = $storagePath.'/'.$directory;

    if (! is_dir($directoryPath) && ! mkdir($directoryPath, 0755, true) && ! is_dir($directoryPath)) {
        throw new RuntimeException('Unable to create the serverless storage directory.');
    }
}

foreach ([
    'LARAVEL_STORAGE_PATH' => $storagePath,
    'APP_CONFIG_CACHE' => $storagePath.'/bootstrap/cache/config.php',
    'APP_EVENTS_CACHE' => $storagePath.'/bootstrap/cache/events.php',
    'APP_PACKAGES_CACHE' => $storagePath.'/bootstrap/cache/packages.php',
    'APP_ROUTES_CACHE' => $storagePath.'/bootstrap/cache/routes.php',
    'APP_SERVICES_CACHE' => $storagePath.'/bootstrap/cache/services.php',
    'VIEW_COMPILED_PATH' => $storagePath.'/framework/views',
] as $name => $value) {
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
    putenv($name.'='.$value);
}

require_once __DIR__.'/../vendor/autoload.php';

TrustProxies::at('*');
TrustProxies::withHeaders(Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

require __DIR__.'/index.php';
