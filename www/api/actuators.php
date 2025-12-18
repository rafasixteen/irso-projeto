<?php
require_once 'helpers.php';
require_once 'config.php';

define('VALUE_PARAM', 'value');

function handle_actuator($name)
{
	$slug = slugify($name);
	$method = $_SERVER['REQUEST_METHOD'];

	if ($method === 'GET') {
		$latest = read_latest(ACTUATORS_LOG_DIR, $slug);

		if ($latest) {
			respond(200, "Latest state for actuator '$name'", ['actuator' => $slug, 'latest' => $latest]);
		} else {
			respond(404, "Actuator '$name' not found");
		}
	} elseif ($method === 'POST') {
		$value = $_POST[VALUE_PARAM] ?? null;

		if ($value === null) {
			respond(400, 'Missing value');
		}

		write_entry(ACTUATORS_LOG_DIR, $slug, $value);
		respond(200, "Actuator '$name' updated", ['actuator' => $slug, VALUE_PARAM => $value]);
	} else {
		respond(405, 'Method not allowed');
	}
}
