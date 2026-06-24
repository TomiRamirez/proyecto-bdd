<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario_actual = $_SESSION['usuario'];

require_once 'utils.php';
$db = conectarDB();

$stmt = $db->query("SELECT u.run_persona, u.tipo_usuario FROM usuario u WHERE u.email_login = '" . $usuario_actual . "'");
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

$run_usuario = $user_info['run_persona'];
$tipo_usuario = strtolower($user_info['tipo_usuario']);
$es_socio = false;
if (strpos($tipo_usuario, 'socio') !== false) {
    $es_socio = true;
}

if (!$es_socio && $tipo_usuario != 'administrativo' && $tipo_usuario != 'administrador') {
    echo "Acceso denegado.";
    exit;
}

if ($es_socio) {
    $sql = "SELECT r.codigo_reserva, r.fecha_inicio, r.fecha_fin, r.estado, 
                   l.nombre AS cancha, s.nombre AS sucursal, p.nombre_completo AS nombre_socio 
            FROM reserva r 
            JOIN lugar l ON r.codigo_lugar = l.codigo_lugar 
            JOIN sucursal s ON l.codigo_sucursal = s.codigo_sucursal 
            JOIN persona p ON r.run_reservante = p.run 
            WHERE r.run_reservante = '" . $run_usuario . "' 
            ORDER BY r.fecha_inicio DESC";
} else {
    $sql = "SELECT r.codigo_reserva, r.fecha_inicio, r.fecha_fin, r.estado, 
                   l.nombre AS cancha, s.nombre AS sucursal, p.nombre_completo AS nombre_socio, p.run AS run_socio 
            FROM reserva r 
            JOIN lugar l ON r.codigo_lugar = l.codigo_lugar 
            JOIN sucursal s ON l.codigo_sucursal = s.codigo_sucursal 
            JOIN persona p ON r.run_reservante = p.run 
            ORDER BY r.fecha_inicio DESC";
}
$reservas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DCColo - Arriendos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>DCColo - Historial de Arriendos</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if ($tipo_usuario == 'administrativo' || $tipo_usuario == 'administrador') { ?>
            <a href="registrar_administrativo.php">Crear Usuario</a>
        <?php } ?>
        <a href="arriendo_canchas.php">Arriendo Canchas</a>
        <a href="mis_arriendos.php">Ver Arriendos</a>
        <a href="crear_evento.php">Crear Evento</a>
        <a href="ver_eventos.php">Ver Eventos</a>
        <a href="logout.php">Cerrar Sesion</a>
    </nav>

    <h2>Arriendos Registrados</h2>

    <?php if (count($reservas) == 0) { ?>
        <p>No se encontraron arriendos.</p>
    <?php } else { ?>
        <table>
            <tr>
                <th>Codigo</th>
                <th>Cancha</th>
                <th>Sucursal</th>
                <th>Socio</th>
                <?php if (!$es_socio) { echo "<th>RUN</th>"; } ?>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
            </tr>
            <?php foreach ($reservas as $r) { ?>
                <tr>
                    <td><?php echo $r['codigo_reserva']; ?></td>
                    <td><?php echo $r['cancha']; ?></td>
                    <td><?php echo $r['sucursal']; ?></td>
                    <td><?php echo $r['nombre_socio']; ?></td>
                    <?php if (!$es_socio) { echo "<td>" . $r['run_socio'] . "</td>"; } ?>
                    <td><?php echo date('d-m-Y H:i', strtotime($r['fecha_inicio'])); ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($r['fecha_fin'])); ?></td>
                    <td><?php echo $r['estado']; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <p><a href="arriendo_canchas.php">Arrendar otra cancha</a> | <a href="index.php">Volver al Inicio</a></p>
</body>
</html>
