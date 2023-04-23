<?php

$usuario = "";
$usercode = "";

if(isset($_POST['u']) && isset($_POST['d']) && isset($_POST['id']))
{
	$usuario = $_POST['u'];
	$usercode = $_POST['d'];
	$serie = $_POST['id'];
}
else
{
	echo 'NOK_Acceso denegado.';
	return;
}

//Conexion con la base de datos 
	$conexionDB = new conexion($usuario,$usercode);
	//conecta con la BD y verifica usercode valido
	$conexion = $conexionDB->conectar();
	
	if(!$conexion)
	{
		echo 'NOK_Acceso denegado BD.';
		return;
	}	
	
	$queryUsr=mysqli_query($conexion,"SELECT idcia from usuarios WHERE idusuario='$usuario'");
	$usr=mysqli_fetch_array($queryUsr);
	$idcia = $usr['idcia'];
	
	//consultar vehiculos asignados
	$con1 = "";
	$equipos = mysqli_query($conexion,"select seriegps from usuarios_equipos where idcia='$idcia' and idusuario='$usuario'");
	while($equipo=mysqli_fetch_array($equipos))
	{
		if($con1!="")
			$con1.=" OR ";
	   $con1 .= "e.seriegps='".$equipo['seriegps']."'";
	}
	if($con1=="")
		$con1="true";
	$con2 = " e.idcia='".$idcia."'"; 
	
	$resp="";
	$query = mysqli_query($conexion,"select 
										c.id, 
										case 
											when c.usuarioActivo is not null then concat(c.nombreCam,' (Viendo por ',uv.nombre,')')
											else c.nombreCam
										end as nombreCam, 
										case 
											when c.status=0 then 'disabled style=''background-color:red'''
											else 'style=''background-color:#92d050'''
										end as estatus,
										concat('class=''',c.tipo,'''') as tipo,
										case 
											when (c.activa=1 or c.usuarioActivo is not null) then 'disabled'
										end as icon
									from equiposGps e
									inner join camaras c on e.seriegps=c.seriegps and c.id_vehiculos=e.id_vehiculos
									left join usuarios uv on c.usuarioActivo=uv.idusuario
									where 
										c.seriegps='$serie' and
										($con1) AND $con2
									order by c.nombreCam");
	while($registro = mysqli_fetch_array($query))
	{
		$resp.="<option value='".$registro['id']."' ".$registro['estatus']." ".$registro['icon']." ".$registro['tipo'].">".$registro['nombreCam']."</option>";
	}
	if($resp!="")
		$resp = "OK_".$resp;
	
	mysqli_close($conexion);
	echo $resp;
?>
