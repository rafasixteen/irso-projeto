<?php
session_start();

$USERNAME = 'admin';
$PASSWORD = '123';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$inputUser = $_POST['username'] ?? '';
	$inputPass = $_POST['password'] ?? '';

	if ($inputUser === $USERNAME && $inputPass === $PASSWORD) {
		$_SESSION['logged_in'] = true;
		header('Location: /');
		exit();
	} else {
		$error = 'Invalid username or password';
	}
}

$view = __DIR__ . '/../pages/login.php';
$styles = ['/assets/css/login.css'];

ob_start();
require $view;
$content = ob_get_clean();

$title = 'Login';
require __DIR__ . '/../layouts/root.php';