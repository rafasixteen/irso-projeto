<?php
session_start();

if (!isset($_SESSION['auth'])) {
	header('Location: index.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Dashboard</title>
</head>

<body>

	<h1>Sensores e Atuadores</h1>

	<a href="logout.php">Logout</a>

</body>

</html>