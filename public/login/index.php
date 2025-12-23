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
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Login</title>
</head>

<body>
	<h1>Login</h1>
	<?php if ($error): ?>
	<p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
	<?php endif; ?>
	<form method="POST" action="">
		<label>Username: <input type="text" name="username" required></label><br><br>
		<label>Password: <input type="password" name="password" required></label><br><br>
		<button type="submit">Login</button>
	</form>
</body>

</html>