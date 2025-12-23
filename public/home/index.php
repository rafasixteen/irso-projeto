<?php
require __DIR__ . '/../../src/auth.php';

require_login();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Home</title>
</head>

<body>
	<h1>Welcome to the Home Page!</h1>
	<p>You are logged in.</p>
	<a href="logout">Logout</a>
</body>

</html>