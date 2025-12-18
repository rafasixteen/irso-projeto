<?php

define('LOGS_DIR', __DIR__ . '/logs');
define('SENSORS_LOG_DIR', LOGS_DIR . '/sensors');
define('ACTUATORS_LOG_DIR', LOGS_DIR . '/actuators');
define('LOG_DIR', LOGS_DIR . '/http_errors');
define('LOG_FILE', LOG_DIR . '/http_errors.json');

define('API_TOKEN', md5('ProjetoIRSO'));

foreach ([SENSORS_LOG_DIR, ACTUATORS_LOG_DIR, LOG_DIR] as $dir) {
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
}
