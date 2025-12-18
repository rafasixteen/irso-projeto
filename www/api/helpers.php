<?php
function slugify($text)
{
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);
	$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
	$text = preg_replace('~[^-\w]+~', '', $text);
	$text = trim($text, '-');
	$text = preg_replace('~-+~', '-', $text);
	return strtolower($text);
}

function respond($status, $message, $extra = [])
{
	http_response_code($status);

	$response = [
		'status' => $status,
		'message' => $message,
		'timestamp' => date('Y-m-d H:i:s'),
	];
	if (!empty($extra)) {
		$response['data'] = $extra;
	}

	echo json_encode($response, JSON_PRETTY_PRINT);

	if ($status < 200 || $status >= 300) {
		log_http_error($status, $message);
	}

	exit();
}

function log_http_error($status, $message)
{
	$entry = [
		'timestamp' => date('Y-m-d H:i:s'),
		'status' => $status,
		'message' => $message,
		'method' => $_SERVER['REQUEST_METHOD'] ?? '',
		'uri' => $_SERVER['REQUEST_URI'] ?? '',
	];

	$log = [];
	if (file_exists(LOG_FILE)) {
		$log = json_decode(file_get_contents(LOG_FILE), true) ?? [];
	}

	$log[] = $entry;
	file_put_contents(LOG_FILE, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function read_latest($dir, $slug)
{
	$file = "$dir/$slug.json";
	if (!file_exists($file)) {
		return null;
	}
	$data = json_decode(file_get_contents($file), true);
	return empty($data) ? null : end($data);
}

function write_entry($dir, $slug, $value)
{
	$file = "$dir/$slug.json";
	$entry = [
		'timestamp' => date('d-m-Y H:i:s'),
		'value' => $value,
	];
	$data = [];
	if (file_exists($file)) {
		$data = json_decode(file_get_contents($file), true) ?? [];
	}
	$data[] = $entry;
	file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function check_auth()
{
	$headers = getallheaders();
	$auth = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
	$token = '';
	if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
		$token = trim($matches[1]);
	}

	if ($token !== API_TOKEN) {
		respond(401, 'Unauthorized');
	}
}
