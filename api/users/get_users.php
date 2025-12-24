<?php
declare(strict_types=1);

header('Content-Type: application/json');

use App\Users\UserService;

$userService = new UserService();
$users = $userService->get_users();

echo json_encode($users);