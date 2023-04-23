<?php
$usuario = "";
$usercode = "";

if(isset($_GET['u']) && isset($_GET['d']))
{
	$usuario = $_GET['u'];
	$usercode = $_GET['d'];
}
else	
	return;

//Conexion con la base de datos 
	include('../ConexionDB/conexion.php');
	$conexionDB = new conexion($usuario,$usercode);
	//conecta con la BD y verifica usercode valido
	$conexion = $conexionDB->conectar();
	
	if(!$conexion)
	{
		echo 'Acceso denegado.';
		return;
	}	
	
	$queryKeyMap = mysqli_query($conexion,"select google_maps_key from servidores_web where ip='".$_SERVER['SERVER_ADDR']."' and servicio='Map'");
	$keyMap = mysqli_fetch_array($queryKeyMap);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Visor camara</title>
 
<!-- estilo -->
	<link rel="stylesheet" href="../css/estilo.css">
<!-- mapa -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $keyMap['google_maps_key'];?>"></script>
<!--botones -->
 <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
 <!--jquery -->
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
 <!--bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
 
 <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
 
<style type="text/css">
<!--
.visible {
  visibility: visible;
}
.invisible {
  visibility: hidden;
}
#map_canvas
{
	margin: 0;
	padding: 0;
	height: 500px;
}
.loader {
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
-->
</style>
</head>
<body>
<?php
	echo "<script>
			var usuario = '".$usuario."';
			var usercode = '".$usercode."';
		</script>";
	$queryRuta = mysqli_query($conexion,"select concat(serv.ip,'/',f.ruta) as ruta 
										from funciones_versogis f inner join servidores_web serv on f.servidor=serv.id
										where f.funcion='Camaras datos' and f.estado='inactivo'");
	if(mysqli_num_rows($queryRuta)!=1)
	{
		echo "<script> alert('No es posible visualizar camaras en este momento.'); </script>";
		return;
	}
	$ruta = mysqli_fetch_array($queryRuta);
	
	$queryUsr=mysqli_query($conexion,"SELECT idcia,identificacionVeh from usuarios WHERE idusuario='$usuario'");
	$usr=mysqli_fetch_array($queryUsr);
	$idcia = $usr['idcia'];
	$identiVeh=$usr['identificacionVeh'];
	

	$selectDatos = mysqli_query($conexion,"
					select 
						e.seriegps,v.placas,v.num_economico,c.alias as aliasConductor, concat(v.num_economico,'_',c.alias) as economicoAlias, 
						cam.id as idCamara,cam.nombreCam,cam.fechaHoraActivo,
						concat('".$ruta['ruta']."',cam.puerto,'/',cam.puerto,'_',LPAD(month(date(cam.fechaHoraActivo)),2,'0'),'_',LPAD(day(date(cam.fechaHoraActivo)),2,'0'),'_',year(date(cam.fechaHoraActivo)),'_',cam.indice,'.init') as ruta
					from vehiculos v
					inner join equiposGps e on e.id_vehiculos=v.id and e.idcia=v.id_cia
					inner join camaras cam on cam.seriegps=e.seriegps and cam.id_vehiculos=v.id
					left join versogis_conductores as c on v.id_conductor=c.idconductor 
					where cam.usuarioActivo='$usuario' and e.idcia='$idcia' and cam.status=1");
	$datos = mysqli_fetch_array($selectDatos);
	echo "<script>
			var videoSource='http://".$datos['ruta']."';
			var id = ".$datos['idCamara'].";
			var serie = '".$datos['seriegps']."';
		</script>";
	mysqli_close($conexion);
?>
<div class="container-fluid" style="padding: 5px;">
	<div class="row" style="padding: 5px;">
		<div class="col-sm-4">
			<div class="row">
				<div class="col-sm-8 degradado4">
				<t2 class="styleHeader ">Veh&iacuteculo:</t2>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<t2 class="styleCampo"><?php echo ucfirst($identiVeh);?>:</t2>
				</div>
				<div class="col-sm-6">
					<t2 class="styleText"><?php echo $datos[$identiVeh];?></t2>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<t2 class="styleCampo">Conductor:</t2>
				</div>
				<div class="col-sm-6">
					<t2 class="styleText"><?php echo $datos['aliasConductor'];?></t2>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="row">		
				<div class="col-sm-8 degradado4">
					<t2 class="styleHeader">C&aacutemara:</t2>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleCampo">Nombre:</t2>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleCampo">Visualizando desde:</t2>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleText"><button class="boton" id="btnDetener">Detener</button></t2>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleText"><?php echo $datos['nombreCam'];?></t2>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleText"><?php echo $datos['fechaHoraActivo'];?></t2>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="row">
				<div class="col-sm-8 degradado4">
				<t2 class="styleHeader ">Ubicaci&oacuten:</t2>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleCampo">Fecha y hora:</t2>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleCampo">Estatus:</t2>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleText" id="lblFechaHr"></t2>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<t2 class="styleText" id="lblEstatus"></t2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row" style="padding: 5px;" id="divLoad">
		<div class="col-sm-12">
			<center><div class="loader"></div>
		</div>
	</div>
	<div class="row" style="padding: 5px;" id="divRep">
		<div class="col-sm-6 invisible" id="divMap">
			<div id="map_canvas"></div>
		</div>
		<div class="col-sm-6 invisible" id="divVideo">
			<video id="video" autoplay="true" controls="controls"></video>
		</div>
	</div>
</div>

</body>
<script>
	consultaVideo();
	function consultaVideo()
	{
		$.ajax({
			type:"POST",
			url:"isVideoLoaded.php",
			data:{
				u: usuario,
				d: usercode,
				id: id
			}
		}).done(function(respuesta)
			{
				respuesta = respuesta.split("_");
				if(respuesta[0]=="OK" && respuesta[1]==1)
				{
					muestraVideo();
				}
				else
				{
					setTimeout(consultaVideo(), 10000);
				}
			});
	}
	function mostrar()
	{
		$("#divMap").removeClass("invisible").addClass("visible");
		$("#divVideo").removeClass("invisible").addClass("visible");
		$("#divMap").removeClass("col-sm-0").addClass("col-sm-6");
		$("#divVideo").removeClass("col-sm-0").addClass("col-sm-6");
		$("#divLoad").remove();
	}
	function muestraVideo()
	{
		mostrar();
		if (Hls.isSupported()) {
		  var video = document.getElementById('video');
		  var hls = new Hls();
		  // bind them together
		  hls.attachMedia(video);
		  hls.on(Hls.Events.MEDIA_ATTACHED, function () {
			console.log("video and hls.js are now bound together !");
			hls.loadSource(videoSource);
			hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
			  console.log("manifest loaded, found " + data.levels.length + " quality level");
			});
		  });
		  video.autoplay = 'autoplay';
		}
	}
  
  $("#btnDetener").button({
		icons:{
			primary: "ui-icon-stop" 
		},
		text: true
	}).click(function(){
		desactivaCamara();
	});

	function desactivaCamara()
	{
		$.ajax({
			type:"POST",
			url:"Videos/unsetPort.php",
			data:{
				u: usuario,
				d: usercode,
				id: id
			}
			}).done(function(respuesta)
				{
					respuesta = respuesta.split("_");
					if(respuesta[0]!="NOK")
					{
						$("#btnCargar", parent.document).attr("disabled",false);
						$("#frameReporte", parent.document).attr("src","");
						window.parent.mostrar();
					}
					else
						alert(respuesta[1]);
				});
	}
	var json_geocercas=null;
	var arr_geocercas = new Array();
	var map,bounds,ventanaInf,trafico,clima;
	var zoom=14;
	var setTrafico;
	var setGeocerca = false;
	var geocoder = new google.maps.Geocoder();
	var marker;
	var markers = new Array();
	var estelasCirc = new Array();
		estelasCirc[0] = new Array();
	var estelasLine = new Array();
		estelasLine[0] = new Array();
	var indiceRep = 0;
	var reproduciendo = true;
	var reproduciendoUnico = false;
	var getDireccion=false;
	var direccion="";
	var referencia=null;
	var cerca=null;
	var linea;
	var timeout = 6000;
	google.maps.event.addDomListener(window, 'load', initialize);
	function initialize() 
	{
		ajustaTamanio();
		bounds = new google.maps.LatLngBounds();
		var latlng= new google.maps.LatLng(19.286192969894312, -98.4400749206543);
		myOptions = {
		  zoom: zoom,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP,gestureHandling: 'greedy' 
		};

		map = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
		google.maps.event.addListener(map, 'click', function(event) {
			
			if(ventanaInf)
				ventanaInf.close();
			
			$( ".btn" ).button({
			 icons: {},
			  text: true
			}).attr("style","color:'#222222';background:'';");
		
		 });
		google.maps.event.addListener(map, 'zoom_changed', function(event) {
			zoom = map.getZoom();
		 });
		
	
		if(!bounds.isEmpty())
			map.fitBounds(bounds);
		
		//reproducir
		if(reproduciendo)
			actualizaPosicion();
		
	//Boton geocercas
	  var geoControlDiv = document.createElement('div');
	  var geoControl = new GeocercaControl(geoControlDiv, map);
	  geoControlDiv.index = 1;
	  map.controls[google.maps.ControlPosition.TOP_LEFT].push(geoControlDiv);		
		
	//Boton trafico
	  var traficControlDiv = document.createElement('div');
	  var traficControl = new TraficControl(traficControlDiv, map);
	  traficControlDiv.index = 2;
	  map.controls[google.maps.ControlPosition.TOP_LEFT].push(traficControlDiv);

	}
	function ajustaTamanio()
	{
		var newHeight = ($(window).height()-80)*.90;
		var widthMapVid = ($(window).width()/2)-10;

		$("#map_canvas").css("height",newHeight+"px");
		$("#map_canvas").css("width",widthMapVid-10+"px");
		
		$("#video").attr("height",newHeight+"px");
		$("#video").attr("width",widthMapVid-10+"px");

	}
	$( window ).resize(function() {
	   ajustaTamanio();
	});
	function TraficControl(controlDiv, map) 
	{
	  // Set CSS styles for the DIV containing the control
	  // Setting padding to 5 px will offset the control
	  // from the edge of the map
	  controlDiv.style.padding = '5px';

	  // Set CSS for the control border
	  var controlUI = document.createElement('div');
	  controlUI.style.backgroundColor = 'white';
	  controlUI.style.borderStyle = 'solid';
	  controlUI.style.borderWidth = '2px';
	  controlUI.style.cursor = 'pointer';
	  controlUI.style.textAlign = 'center';
	  controlUI.title = 'Mostrar/Ocultar trafico';
	  controlUI.id="btnTrafico";
	  controlDiv.appendChild(controlUI);

	  // Set CSS for the control interior
	  var controlText = document.createElement('div');
	  controlText.style.fontFamily = 'Helvetica';
	  controlText.style.fontSize = '12px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = '<b>Trafico</b>';
	  controlUI.appendChild(controlText);

	  google.maps.event.addDomListener(controlUI, 'click', function() {
		showTrafic();
	  });
	}
	function GeocercaControl(controlDiv, map) 
	{
	  // Set CSS styles for the DIV containing the control
	  // Setting padding to 5 px will offset the control
	  // from the edge of the map
	  controlDiv.style.padding = '5px';

	  // Set CSS for the control border
	  var controlUI = document.createElement('div');
	  controlUI.style.backgroundColor = 'white';
	  controlUI.style.borderStyle = 'solid';
	  controlUI.style.borderWidth = '2px';
	  controlUI.style.cursor = 'pointer';
	  controlUI.style.textAlign = 'center';
	  controlUI.title = 'Mostrar/Ocultar geocercas';
	  controlUI.id="btnGeocerca";
	  controlDiv.appendChild(controlUI);

	  // Set CSS for the control interior
	  var controlText = document.createElement('div');
	  controlText.style.fontFamily = 'Helvetica';
	  controlText.style.fontSize = '12px';
	  controlText.style.paddingLeft = '4px';
	  controlText.style.paddingRight = '4px';
	  controlText.innerHTML = '<b>Geocercas</b>';
	  controlUI.appendChild(controlText);

	  google.maps.event.addDomListener(controlUI, 'click', function() {
		setGeocerca=!setGeocerca;
		showGeocercas();
	  });
	}
	
	function showTrafic()
	{
		if(!setTrafico)//no esta mostrado el trafico
		{
			$("#btnTrafico").css("backgroundColor","green");
			$("#btnTrafico").css("color","white");
			trafico = new google.maps.TrafficLayer();
			trafico.setMap(map);
			setTrafico = true;
			$("#traf").val("true");
		}
		else
		{
			$("#btnTrafico").css("backgroundColor","white");
			$("#btnTrafico").css("color","black");
			trafico.setMap(null);
			setTrafico=false;
			$("#traf").val("false");
		}	
	}
	function showGeocercas()
	{
		if(setGeocerca)
		{
			$("#btnGeocerca").css("backgroundColor","green");
			$("#btnGeocerca").css("color","white");
			for(i=0; arr_geocercas.length; i++)
			{
				arr_geocercas[i].setMap(map);
			}
			setGeocerca = true;
		}
		else
		{
			$("#btnGeocerca").css("backgroundColor","white");
			$("#btnGeocerca").css("color","black");
			for(i=0; arr_geocercas.length; i++)
			{
				arr_geocercas[i].setMap(null);
			}
			setGeocerca=false;
		}	
	}

	function actualizaPosicion()
	{
		var i=0;
		$.ajax(
		{
			type:"post",
			url:"../API/consultaLastpos_json.php",
			data:{
				u: usuario,
				d: usercode,
				seriegps: serie
			}
		}).done(function(resp) {
			resp = JSON.parse(resp + "");
			if(resp['success']=="ok") 
			{
				$("#lblFechaHr t").remove();
				$("#lblFechaHr").append("<t>"+resp['fecha_hora']+"</t>");
				$("#lblEstatus t").remove();
				$("#lblEstatus").append("<t>"+resp['estatus']+" vel: "+resp['lastvel']+" km/h</t>");
				if(marker)
				{
					var lastLatLng = marker.position;
					marker.setIcon("../Markers/Vehiculos/"+resp['color_icono']+"_"+resp['tipo_unidad']+".png");
					
					if(resp['lastlat']!=lastLatLng.lat() && resp['lastlon']!=lastLatLng.lng())//dejar estela
					{
						marker.setPosition(new google.maps.LatLng(resp['lastlat'],resp['lastlon']));
					
						var arr = colocaEstela(lastLatLng,new google.maps.LatLng(resp['lastlat'],resp['lastlon']),"../Rastreo/Maximizado/estela2.png");
			
						estelasCirc[i].push(arr[0]);
						estelasLine[i].push(arr[1]);
						if(estelasCirc[i].length>5)
						{
							estelasCirc[i].shift().setMap(null);
							estelasLine[i].shift().setMap(null);
						}
						map.setCenter(marker.position);
						map.setZoom(zoom);
					}
				}
				else
				{
					var pos = new google.maps.LatLng(resp['lastlat'],resp['lastlon']);
					bounds.extend(pos);
					marker = new google.maps.Marker({
					  position: pos, 
					  map: map,
					  icon: "../Markers/Vehiculos/"+resp['color_icono']+"_"+resp['tipo_unidad']+".png",
					  zindex: i,
					  title:serie,
					  label: {
						  text: ""+(i+1),
						  color: "#C0C0C0",
						  fontSize: "15px",
						  fontWeight: "bold"
						}
				   });
				    map.setCenter(marker.position);
					map.setZoom(zoom);
				}
				actualizaGeocercas();
				setTimeout(function(){actualizaPosicion()},timeout);
			}
			else	
				alert(resp['message']);
		});
	}
	function actualizaGeocercas()
	{
		var i=0;
		$.ajax(
		{
			type:"post",
			url:"../API/geocercasCercanas_json.php",
			data:{
				u: usuario,
				d: usercode,
				seriegps: serie
			}
		}).done(function(resp) {
			if(resp!=json_geocercas)
			{
				quita_geocercas();
				arr_geocercas = new Array();
				json_geocercas = resp;
				resp = JSON.parse(resp + "");
				if(resp['success']=="ok") 
				{
					for(i=0; i<resp['geocercas'].length; i++)
					{
						var path = new Array();
						
						for(j=0; j<resp['geocercas'][i]['puntos'].length; j++)
						{
							var latlon = new google.maps.LatLng(resp['geocercas'][i]['puntos'][j]['latitud'],resp['geocercas'][i]['puntos'][j]['longitud']);
							path.push(latlon);
						}
						var geo = new google.maps.Polygon({
						  path: new google.maps.MVCArray(),
						  strokeColor: '#EE4000',
						  strokeOpacity: 0.8,
						  strokeWeight: 3,
						  fillColor: '#EE4000',
						  fillOpacity: 0.3
						});
						geo.setPath(path);
						if(setGeocerca)
							geo.setMap(map);
						/*
						google.maps.event.addListener(geo, 'click', function(event) {
						   if(ventanaInf)
								ventanaInf.close();
						*/	ventanaInf = new google.maps.InfoWindow({
							   content: resp['geocercas'][i]["nombre"],
							   position: new google.maps.LatLng(resp['geocercas'][i]['puntos'][0]['latitud'],resp['geocercas'][i]['puntos'][0]['longitud'])
						   });
						  // ventanaInf.open(map);
						// });
						arr_geocercas.push(geo);
					}
				}
				else	
					alert(resp['message']);
			}
		});
	}
	function quita_geocercas()
	{
		if(arr_geocercas.length>0)
		{
			for(i=0; i<arr_geocercas.length; i++)
			{
				arr_geocercas[i].setMap(null);
			}
		}
	}
	function colocaEstela(lastLatLng,curLatlng, icono)
	{
		// Add the circle
		var circle = new google.maps.Circle({
		  strokeColor: '#FF0000',
		  strokeOpacity: 0.8,
		  strokeWeight: 2,
		  fillColor: '#FF0000',
		  fillOpacity: 0.35,
		  map: map,
		  center: lastLatLng,
		  radius: 10
		});
		// Add the line
		var path = [lastLatLng,curLatlng];
		 var line = new google.maps.Polyline({
			path: path,
			geodesic: true,
			strokeColor: '#FF0000',
			strokeOpacity: 1.0,
			strokeWeight: 2,
			map:map
		  });
		bounds.extend(curLatlng);
		return [circle,line];
	}
	
	
$(document).ready(function(){
    
 });
	
	function Linea(puntosPath,inf)
	{
         linea = new google.maps.Polyline({
          path: puntosPath,
          strokeColor: "#0077B0",
          strokeOpacity: 1.0,
          strokeWeight: 6,
		  map: map
         });
		 google.maps.event.addListener(linea, 'click', function(event) {
		   var ventanaInf = new google.maps.InfoWindow({
		   content: inf,
		   position: event.latLng,
						   pixelOffset: new google.maps.Size(0, -30)
		   });
           ventanaInf.open(map);
         });
	}
	
</script>

</html>
