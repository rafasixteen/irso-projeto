<?php
define('API_TOKEN', md5('ProjetoIRSO'));

define('SENSORS_LOG_DIR', __DIR__ . '/sensor_logs');
define('LOG_DIR', __DIR__ . '/http_logs');
define('LOG_FILE', LOG_DIR . '/http_errors.json');

define('NAME_PARAM', 'nome');
define('VALUE_PARAM', 'valor');

header('Content-Type: application/json');

function log_http_error($method, $message)
{
	if (!is_dir(LOG_DIR)) {
		mkdir(LOG_DIR, 0777, true);
	}

	$entry = [
		'method' => $method,
		'message' => $message,
		'timestamp' => date('d-m-Y H:i:s'),
	];

	$errors = [];
	if (file_exists(LOG_FILE)) {
		$content = file_get_contents(LOG_FILE);
		$errors = json_decode($content, true) ?? [];
	}

	$errors[] = $entry;

	file_put_contents(LOG_FILE, json_encode($errors, JSON_PRETTY_PRINT));
}

function respond($status, $message, $extra = [])
{
	http_response_code($status);

	$response = [
		'status' => $status,
		'message' => $message,
	];

	if (!empty($extra)) {
		$response['data'] = $extra;
	}

	echo json_encode($response);
	exit();
}

function unauthorized($method)
{
	log_http_error($method, 'Unauthorized');
	respond(401, 'Unauthorized');
}

function save_sensor_data($nome, $valor)
{
	$logDir = __DIR__ . '/sensor_logs';

	if (!is_dir($logDir)) {
		mkdir($logDir, 0777, true);
	}

	$filePath = $logDir . '/' . $nome . '.json';

	$entry = [
		'timestamp' => date('d-m-Y H:i:s'),
		'value' => $valor,
	];

	$data = [];
	if (file_exists($filePath)) {
		$content = file_get_contents($filePath);
		$data = json_decode($content, true) ?? [];
	}

	$data[] = $entry;

	file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

function read_latest_sensor_data($nome)
{
	$filePath = SENSORS_LOG_DIR . '/' . $nome . '.json';

	if (!file_exists($filePath)) {
		return null;
	}

	$content = file_get_contents($filePath);
	$data = json_decode($content, true);

	if (empty($data)) {
		return null;
	}

	return end($data);
}

function slugify($text)
{
	// Replace non-letter/digit characters with hyphens
	$text = preg_replace('~[^\pL\d]+~u', '-', $text);

	// Transliterate UTF-8 to ASCII
	$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

	// Remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	// Trim hyphens from ends
	$text = trim($text, '-');

	// Remove duplicate hyphens
	$text = preg_replace('~-+~', '-', $text);

	// Lowercase
	$text = strtolower($text);

	return $text;
}

// ================= AUTHENTICATION =================
$method = $_SERVER['REQUEST_METHOD'];

$headers = getallheaders();
$auth = $headers['Authorization'] ?? ($headers['authorization'] ?? '');

$token = '';

if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
	$token = trim($matches[1]);
}

if ($token !== API_TOKEN) {
	unauthorized($method);
	exit();
}

// ================= HANDLER FUNCTIONS =================
function handle_get()
{
	$nome = $_GET[NAME_PARAM] ?? '';
	$nome = slugify($nome);

	if ($nome === '') {
		$msg = 'Missing ' . NAME_PARAM . ' parameter';
		log_http_error('GET', $msg);
		respond(400, $msg);
	}

	$data = read_latest_sensor_data($nome);

	if ($data !== null) {
		$responseData = [
			'sensor' => $nome,
			'latest' => $data,
		];

		respond(200, 'Latest reading retrieved', $responseData);
	} else {
		log_http_error('GET', "No readings found for sensor '$nome'");
		respond(404, "No readings found for sensor '$nome'");
	}
}

function handle_post()
{
	$nome = $_POST['nome'] ?? ($_GET['nome'] ?? '');
	$valor = $_POST['valor'] ?? ($_GET['valor'] ?? '');

	if ($nome === '') {
		log_http_error('POST', 'Missing ' . NAME_PARAM);
		respond(400, 'Missing ' . NAME_PARAM . ' parameter');
	}

	if ($valor === '') {
		log_http_error('POST', 'Missing ' . VALUE_PARAM);
		respond(400, 'Missing ' . VALUE_PARAM . ' parameter');
	}

	$nome = slugify($nome);
	save_sensor_data($nome, $valor);

	respond(200, 'Valor registado', ['nome' => $nome, 'valor' => $valor]);
}

// ================= ROUTING =================
if ($method === 'GET') {
	handle_get();
} elseif ($method === 'POST') {
	handle_post();
} else {
	log_http_error($method, 'Method not allowed');
	respond(405, 'Method not allowed');
}
