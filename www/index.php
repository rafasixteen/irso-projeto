<?php
session_start();

if (isset($_SESSION['auth'])) {
	header('Location: dashboard.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>IRSO - Projeto</title>
</head>

<body>

	<form method="post" action="login.php">
		<label>Utilizador:</label>
		<input type="text" name="user" required>

		<label>Password:</label>
		<input type="password" name="pass" required>

		<button type="submit">Entrar</button>
	</form>

</body>

</html>