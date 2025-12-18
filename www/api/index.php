<?php
require_once 'config.php';
require_once 'helpers.php';

check_auth();

header('Content-Type: application/json');

$path = trim($_SERVER['REQUEST_URI'], '/');
$segments = explode('/', $path);

if (count($segments) < 3) {
	respond(400, 'Invalid URL');
}

$type = $segments[1];
$name = $segments[2];

if ($type === 'sensor') {
	require_once 'sensors.php';
	handle_sensor($name);
} elseif ($type === 'actuator') {
	require_once 'actuators.php';
	handle_actuator($name);
} else {
	respond(400, 'Invalid type');
}
