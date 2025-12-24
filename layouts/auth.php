<?php
session_start();

// Redirect if not logged in
if (empty($_SESSION['logged_in'])) {
	header('Location: /login');
	exit();
}

// Ensure $styles exists
if (empty($styles)) {
	$styles = [];
}

// Automatically include navbar CSS
$navbarCss = '/assets/css/shared/navbar.css';
if (!in_array($navbarCss, $styles)) {
	$styles[] = $navbarCss;
}

// Capture page content
ob_start();

// Include navbar automatically
require __DIR__ . '/../partials/navbar.php';

// Include the page content
require $view;

$content = ob_get_clean();

// Render root layout
require __DIR__ . '/root.php';
