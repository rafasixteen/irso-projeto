<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Users\UserService;

$userService = new UserService();
$user = $userService->get_user_by_id($id);

if ($user === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "User '{$id}' not found",
		],
	]);
	exit();
} else {
	http_response_code(200);
	echo json_encode($user);
}