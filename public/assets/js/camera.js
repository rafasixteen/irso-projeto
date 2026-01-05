const img = document.getElementById('cameraImage');
const refreshInterval = 2000;

setInterval(() => {
	const timestamp = new Date().getTime(); // cache-buster
	img.src = `http://localhost:8000/assets/images/camera_snapshot.jpg?ts=${timestamp}`;
}, refreshInterval);
