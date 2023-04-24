<?php
// primer archvio generado por desarrollador IA 
// Cambiar aquí la ruta y nombre del archivo de video histórico
$video_file = "/ruta/al/video.mp4";
?>

<!DOCTYPE html>
<html>
<head>
	<title>Visor de video histórico</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css">
		body {
			margin: 0;
			padding: 0;
			background-color: #000;
			color: #fff;
			font-family: Arial, sans-serif;
			font-size: 16px;
			line-height: 1.5;
			text-align: center;
		}
		#video {
			width: 100%;
			max-width: 800px;
			margin: 0 auto;
			display: block;
		}
		#controls {
			margin-top: 20px;
		}
		button {
			background-color: #fff;
			color: #000;
			border: none;
			padding: 10px 20px;
			font-size: 16px;
			cursor: pointer;
			margin-right: 10px;
			border-radius: 5px;
		}
		button:hover {
			background-color: #ccc;
		}
	</style>
</head>
<body>
	<h1>Visor de video histórico</h1>
	<video id="video" controls autoplay>
		<source src="<?php echo $video_file; ?>" type="video/mp4">
	</video>
	<div id="controls">
		<button onclick="video.currentTime -= 10">Atrás 10 segundos</button>
		<button onclick="video.currentTime += 10">Adelante 10 segundos</button>
		<button onclick="video.paused ? video.play() : video.pause()"><?php echo $video_paused ? 'Play' : 'Pausa'; ?></button>
	</div>
	<script type="text/javascript">
		var video = document.getElementById("video");
		var controls = document.getElementById("controls");
		video.addEventListener("play", function() {
			controls.style.display = "block";
		});
		video.addEventListener("pause", function() {
			controls.style.display = "block";
		});
		video.addEventListener("ended", function() {
			controls.style.display = "none";
		});
	</script>
</body>
</html>
