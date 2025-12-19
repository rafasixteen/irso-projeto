<?php
declare(strict_types=1);

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$tag = $body['tag'] ?? null;
$door = $body['door'] ?? null;

if (empty($tag) || empty($door)) {
	http_response_code(400);
	echo json_encode([
		'error' => [
			'message' => 'Invalid request format',
			'expected' => [
				'tag' => 'TAG',
				'door' => 'DOOR',
			],
		],
	]);
	exit();
}

$authorized = true;
$user = 'rafasixteen';
$door = 'main-entrance';

echo json_encode([
	'autorized' => $authorized,
	'user' => $user,
	'door' => $door,
]);
