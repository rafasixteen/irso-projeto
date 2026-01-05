<?php $error = $error ?? ''; ?>

<div class="login-container">
	<h1>Login</h1>

	<?php if ($error): ?>
	<p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
	<?php endif; ?>


	<form method="POST" action="">
		<label>Username: <input type="text" name="username" required></label><br><br>
		<label>Password: <input type="password" name="password" required></label><br><br>
		<button type="submit">Login</button>
	</form>
</div>