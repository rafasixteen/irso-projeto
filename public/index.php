<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// TODO: Add authorization middleware.

require __DIR__ . '/../vendor/autoload.php';

$router = new AltoRouter();

// Sensor API routes

$router->map('GET', '/api/sensor/[a:name]', function ($name) {
	require __DIR__ . '/../api/sensor/get.php';
});

$router->map('GET', '/api/sensors', function () {
	require __DIR__ . '/../api/sensor/get-all.php';
});

$router->map('POST', '/api/sensor/[a:name]', function ($name) {
	require __DIR__ . '/../api/sensor/set.php';
});

// Actuator API routes

$router->map('GET', '/api/actuator/[a:name]', function ($name) {
	require __DIR__ . '/../api/actuator/get.php';
});

$router->map('GET', '/api/actuators', function () {
	require __DIR__ . '/../api/actuator/get-all.php';
});

$router->map('POST', '/api/actuator/[a:name]', function ($name) {
	require __DIR__ . '/../api/actuator/set.php';
});

$match = $router->match();

if (is_array($match) && is_callable($match['target'])) {
	call_user_func_array($match['target'], $match['params']);
} else {
	http_response_code(404);
}
