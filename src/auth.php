<?php
session_start();

function require_login(string $redirect = '/login'): void
{
	if (empty($_SESSION['logged_in'])) {
		header('Location: ' . $redirect);
		exit();
	}
}
