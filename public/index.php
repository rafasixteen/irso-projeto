<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$router = new AltoRouter();

// Authentication middleware under /api routes

if (str_starts_with($_SERVER['REQUEST_URI'], '/api')) {
	$headers = getallheaders();
	$token = $headers['Authorization'] ?? '';

	// Expect token in format: Bearer <token>
	if (str_starts_with($token, 'Bearer ')) {
		$token = substr($token, 7);
	}

	if ($token !== md5('ProjetoIRSO')) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Unauthorized']);
		exit();
	}
}

$router->map('GET', '/', function () {
	require __DIR__ . '/../public/home/index.php';
});

$router->map('GET', '/home', function () {
	require __DIR__ . '/../public/home/index.php';
});

// User API routes

// Create a new user
$router->map('POST', '/api/users', function () {
	require __DIR__ . '/../api/users/add_user.php';
});

// Get user by ID
$router->map('GET', '/api/users/[i:id]', function ($id) {
	require __DIR__ . '/../api/users/get_user.php';
});

// Update user by ID
$router->map('POST', '/api/users/[i:id]', function ($id) {
	require __DIR__ . '/../api/users/update_user.php';
});

// Delete user by ID
$router->map('DELETE', '/api/users/[i:id]', function ($id) {
	require __DIR__ . '/../api/users/delete_user.php';
});

// Get all users
$router->map('GET', '/api/users', function () {
	require __DIR__ . '/../api/users/get_users.php';
});

// Door API routes

// Create a new door
$router->map('POST', '/api/doors', function () {
	require __DIR__ . '/../api/doors/add_door.php';
});

// Get a door by ID
$router->map('GET', '/api/doors/[*:id]', function ($id) {
	require __DIR__ . '/../api/doors/get_door.php';
});

// Update a door by ID
$router->map('POST', '/api/doors/[*:id]', function ($id) {
	require __DIR__ . '/../api/doors/update_door.php';
});

// Delete a door by ID
$router->map('DELETE', '/api/doors/[*:id]', function ($id) {
	require __DIR__ . '/../api/doors/delete_door.php';
});

// RFID API routes

$router->map('GET', '/api/rfid/scan', function () {
	require __DIR__ . '/../api/rfid/scan.php';
});

$router->map('GET', '/api/rfid/logs', function () {
	require __DIR__ . '/../api/rfid/logs.php';
});

// Sensor API routes

$router->map('GET', '/api/sensor/[a:name]', function ($name) {
	require __DIR__ . '/../api/sensor/get-history.php';
});

$router->map('GET', '/api/sensors', function () {
	require __DIR__ . '/../api/sensor/get-all-names.php';
});

$router->map('POST', '/api/sensor/[a:name]', function ($name) {
	require __DIR__ . '/../api/sensor/append-value.php';
});

// Actuator API routes

$router->map('GET', '/api/actuator/[a:name]', function ($name) {
	require __DIR__ . '/../api/actuator/get-history.php';
});

$router->map('GET', '/api/actuators', function () {
	require __DIR__ . '/../api/actuator/get-all-names.php';
});

$router->map('POST', '/api/actuator/[a:name]', function ($name) {
	require __DIR__ . '/../api/actuator/append-value.php';
});

$match = $router->match();

if (is_array($match) && is_callable($match['target'])) {
	call_user_func_array($match['target'], $match['params']);
} else {
	http_response_code(404);
}