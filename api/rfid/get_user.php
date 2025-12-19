<?php
declare(strict_types=1);

header('Content-Type: application/json');

echo $id;

use App\Service\UserService;

$userService = new UserService();

$success = $userService->delete($id);

if ($success) {
	echo json_encode(['message' => 'User deleted successfully']);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to delete user']);
}