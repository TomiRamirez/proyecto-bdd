<?php
session_start();

$usuario = $_SESSION['usuario'] ?? 'desconocido';
$fecha_hora = date('d-m-Y H:i:s');

# guardamos el log
$archivo = fopen("accesos.log", "a");
fwrite($archivo, "El dia y hora " . $fecha_hora . " el usuario " . $usuario . " cerró sesión.\n");
fclose($archivo);

session_destroy();
header("Location: login.php");
exit;
?>
