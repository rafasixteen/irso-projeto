<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$router = new AltoRouter();

// Authentication middleware under /api routes

function getBearerToken(): string
{
	// 1. Authorization header
	$header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? (getallheaders()['Authorization'] ?? ''));

	if (str_starts_with($header, 'Bearer ')) {
		return substr($header, 7);
	}

	// 2. Body
	$rawBody = file_get_contents('php://input');

	// JSON body
	$json = json_decode($rawBody, true);
	if (json_last_error() === JSON_ERROR_NONE && isset($json['token'])) {
		return trim(str_replace('Bearer ', '', $json['token']));
	}

	// Multipart / raw form-data
	if (preg_match('/name="token"\s+([^\r\n]+)/', $rawBody, $matches)) {
		return trim(str_replace('Bearer ', '', $matches[1]));
	}

	// Standard POST
	if (isset($_POST['token'])) {
		return trim(str_replace('Bearer ', '', $_POST['token']));
	}

	return '';
}

if (str_starts_with($_SERVER['REQUEST_URI'], '/api')) {
	$token = getBearerToken();

	if ($token !== md5('ProjetoIRSO')) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Unauthorized']);
		exit();
	}
}

// Camera image upload route

$router->map('POST', '/api/camera', function () {
	require __DIR__ . '/../api/upload-image.php';
});

// User API routes

// Add a new user
$router->map('POST', '/api/users', function () {
	require __DIR__ . '/../api/users/add_user.php';
});

// Get user by ID
$router->map('GET', '/api/users/[i:id]', function ($id) {
	$id = (int) $id;
	require __DIR__ . '/../api/users/get_user.php';
});

// Update user by ID
$router->map('POST', '/api/users/[i:id]', function ($id) {
	$id = (int) $id;
	require __DIR__ . '/../api/users/update_user.php';
});

// Delete user by ID
$router->map('DELETE', '/api/users/[i:id]', function ($id) {
	$id = (int) $id;
	require __DIR__ . '/../api/users/delete_user.php';
});

// Get all users
$router->map('GET', '/api/users', function () {
	require __DIR__ . '/../api/users/get_users.php';
});

// Door API routes

// Get all doors latest history
$router->map('GET', '/api/doors/history/latest', function () {
	require __DIR__ . '/../api/doors/get_latest_history.php';
});

// Get full history for a specific door
$router->map('GET', '/api/doors/[*:id]/history', function ($id) {
	require __DIR__ . '/../api/doors/get_history.php';
});

// Add a new history entry for a specific door
$router->map('POST', '/api/doors/[*:id]/history', function ($id) {
	require __DIR__ . '/../api/doors/add_history_entry.php';
});

// Add a new door
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

// Get all rfid's latest history
$router->map('GET', '/api/rfids/history/latest', function () {
	require __DIR__ . '/../api/rfids/get_latest_history.php';
});

// Get full history for a specific rfid
$router->map('GET', '/api/rfids/[*:id]/history', function ($id) {
	require __DIR__ . '/../api/rfids/get_history.php';
});

// Add a new history entry for a specific rfid
$router->map('POST', '/api/rfids/[*:id]/history', function ($id) {
	require __DIR__ . '/../api/rfids/add_history_entry.php';
});

// RFID scan
$router->map('POST', '/api/rfids/scan', function () {
	require __DIR__ . '/../api/rfids/scan.php';
});

// Sensor API routes

$router->map('GET', '/api/sensors', function () {
	require __DIR__ . '/../api/sensors/get-latest-history.php';
});

$router->map('GET', '/api/sensors/[a:name]', function ($name) {
	require __DIR__ . '/../api/sensors/get-history.php';
});

$router->map('POST', '/api/sensors/[a:name]', function ($name) {
	require __DIR__ . '/../api/sensors/add-history-entry.php';
});

// Actuator API routes

$router->map('GET', '/api/actuators', function () {
	require __DIR__ . '/../api/actuators/get-latest-history.php';
});

$router->map('GET', '/api/actuators/[a:name]', function ($name) {
	require __DIR__ . '/../api/actuators/get-history.php';
});

$router->map('POST', '/api/actuators/[a:name]', function ($name) {
	require __DIR__ . '/../api/actuators/add-history-entry.php';
});

// Public page routes

$router->map('GET', '/', function () {
	require __DIR__ . '/../public/home.php';
});

$router->map('GET', '/not-found', function () {
	require __DIR__ . '/../public/not_found.php';
});

$router->map('GET', '/users', function () {
	require __DIR__ . '/../public/users.php';
});

$router->map('GET', '/doors', function () {
	require __DIR__ . '/../public/doors.php';
});

$router->map('GET', '/doors/[*:id]', function ($id) {
	require __DIR__ . '/../public/door-history.php';
});

$router->map('GET', '/rfid-sensors', function () {
	require __DIR__ . '/../public/rfid-sensors.php';
});

$router->map('GET', '/rfid-sensors/[*:id]', function ($id) {
	require __DIR__ . '/../public/rfid-sensor-history.php';
});

$router->map('GET', '/sensors', function () {
	require __DIR__ . '/../public/sensors.php';
});

$router->map('GET', '/sensors/[*:name]', function ($name) {
	require __DIR__ . '/../public/sensor-history.php';
});

$router->map('GET', '/actuators', function () {
	require __DIR__ . '/../public/actuators.php';
});

$router->map('GET', '/actuators/[*:name]', function ($name) {
	require __DIR__ . '/../public/actuator-history.php';
});

$router->map('GET', '/logout', function () {
	require __DIR__ . '/../public/logout.php';
});

$router->map('GET', '/login', function () {
	require __DIR__ . '/../public/login.php';
});

$router->map('POST', '/login', function () {
	require __DIR__ . '/../public/login.php';
});

$match = $router->match();

if (is_array($match) && is_callable($match['target'])) {
	call_user_func_array($match['target'], $match['params']);
} else {
	http_response_code(404);
	exit();
}
