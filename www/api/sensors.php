<?php
require_once 'helpers.php';
require_once 'config.php';

define('VALUE_PARAM', 'value');

function handle_sensor($name)
{
	$slug = slugify($name);
	$method = $_SERVER['REQUEST_METHOD'];

	if ($method === 'GET') {
		$latest = read_latest(SENSORS_LOG_DIR, $slug);

		if ($latest) {
			respond(200, "Latest reading for sensor '$name'", ['sensor' => $slug, 'latest' => $latest]);
		} else {
			respond(404, "Sensor '$name' not found");
		}
	} elseif ($method === 'POST') {
		$value = $_POST[VALUE_PARAM] ?? null;

		if ($value === null) {
			respond(400, 'Missing value');
		}

		write_entry(SENSORS_LOG_DIR, $slug, $value);
		respond(200, "Sensor '$name' updated", ['sensor' => $slug, VALUE_PARAM => $value]);
	} else {
		respond(405, 'Method not allowed');
	}
}
