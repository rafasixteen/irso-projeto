<?php
session_start();

$USER = 'admin';
$PASS = '1234';

if ($_POST['user'] === $USER && $_POST['pass'] === $PASS) {
	$_SESSION['auth'] = true;
	header('Location: dashboard.php', true, 302);
} else {
	echo 'Login inválido';
}
