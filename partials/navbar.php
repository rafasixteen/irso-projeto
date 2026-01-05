<?php
$links = [
	'/' => 'Home',
	'/users' => 'Users',
	'/camera' => 'Camera',
	'/doors' => 'Doors',
	'/rfid-sensors' => 'RFID Sensors',
	'/sensors' => 'Sensors',
	'/actuators' => 'Actuators',
	'/logout' => 'Logout',
]; ?>

<nav>
	<?php foreach ($links as $url => $label): ?>
	<a href="<?= $url ?>" class="<?= $currentPage === ltrim($url, '/') ? 'active' : '' ?>">
		<?= $label ?>
	</a>
	<?php endforeach; ?>
</nav>
<hr>