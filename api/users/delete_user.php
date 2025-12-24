<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Users\UserService;

$userService = new UserService();

if ($userService->get_user_by_id($id) === null) {
	http_response_code(404);
	echo json_encode([
		'error' => [
			'code' => 'NOT_FOUND',
			'message' => "User with ID '{$id}' not found",
		],
	]);
	exit();
}

$success = $userService->delete_user_by_id($id);

if ($success) {
	http_response_code(200);
	echo json_encode([
		'message' => 'User deleted successfully',
	]);
	exit();
} else {
	http_response_code(500);
	echo json_encode([
		'error' => [
			'code' => 'SERVER_ERROR',
			'message' => "Failed to delete user with ID '{$id}'",
		],
	]);
}