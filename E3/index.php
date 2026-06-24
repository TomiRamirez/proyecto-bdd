<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario_actual = $_SESSION['usuario'];

require_once 'utils.php';
$db = conectarDB();

$stmt = $db->query("SELECT tipo_usuario FROM usuario WHERE email_login = '" . $usuario_actual . "'");
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
$tipo_usuario = strtolower($user_info['tipo_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DCColo - Panel Principal</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>DCColo - Panel Principal</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if ($tipo_usuario == 'administrativo' || $tipo_usuario == 'administrador') { ?>
            <a href="registrar_administrativo.php">Crear Usuario</a>
            <a href="procesar_login.php?ver=logs">Historial</a>
        <?php } ?>
        <a href="arriendo_canchas.php">Arriendo Canchas</a>
        <a href="mis_arriendos.php">Ver Arriendos</a>
        <a href="crear_evento.php">Crear Evento</a>
        <a href="ver_eventos.php">Ver Eventos</a>
        <a href="logout.php">Cerrar Sesion (<?= htmlspecialchars($usuario_actual) ?>)</a>
    </nav>

    <h2>Acciones Rapidas</h2>
    <ul>
        <li><a href="arriendo_canchas.php">Gestionar Canchas</a> - Modifique horarios y arriende instalaciones.</li>
        <li><a href="mis_arriendos.php">Ver Arriendos</a> - Revise el historial de reservas.</li>
        <li><a href="crear_evento.php">Crear Evento</a> - Configure nuevos torneos, cenas, entre otras cosas.</li>
        <li><a href="ver_eventos.php">Ver Eventos</a> - Listado de todos los eventos registrados.</li>
        <?php if ($tipo_usuario == 'administrativo' || $tipo_usuario == 'administrador') { ?>
            <li><a href="registrar_administrativo.php">Crear Usuario</a> - Gestione permisos para el equipo administrativo.</li>
        <?php } ?>
    </ul>
</body>
</html>