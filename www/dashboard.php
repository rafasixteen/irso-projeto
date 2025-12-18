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

	<h1>Dashboard - Sensores e Atuadores</h1>

	<div>
		<div>
			<h2>Sensores</h2>
			<ul>
				<li>Sensor 1: <span id="sensor1">---</span></li>
				<li>Sensor 2: <span id="sensor2">---</span></li>
				<li>Sensor 3: <span id="sensor3">---</span></li>
				<li>Sensor 4: <span id="sensor4">---</span></li>
				<li>Sensor 5: <span id="sensor5">---</span></li>
				<li>Sensor 6: <span id="sensor6">---</span></li>
			</ul>
		</div>

		<div>
			<h2>Atuadores</h2>
			<ul>
				<li>Atuador 1: <button>ON/OFF</button></li>
				<li>Atuador 2: <button>ON/OFF</button></li>
				<li>Atuador 3: <button>ON/OFF</button></li>
				<li>Atuador 4: <button>ON/OFF</button></li>
				<li>Atuador 5: <button>ON/OFF</button></li>
				<li>Atuador 6: <button>ON/OFF</button></li>
			</ul>
		</div>
	</div>

	<a href="history.php">Ver Histórico</a> | <a href="logout.php">Logout</a>

</body>

</html>