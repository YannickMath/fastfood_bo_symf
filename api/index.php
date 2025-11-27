<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new App\Kernel($_ENV['APP_ENV'] ?? 'prod', (bool) ($_ENV['APP_DEBUG'] ?? false));

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);