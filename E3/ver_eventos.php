<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario_actual = $_SESSION['usuario'];

require_once 'utils.php';
$db = conectarDB();

# obtenemos los datos del usuario actual
$query_usuario = "SELECT u.run_persona, u.tipo_usuario, p.nombre_completo FROM usuario u LEFT JOIN persona p ON u.run_persona = p.run WHERE u.email_login = '" . $usuario_actual . "'";
$stmt = $db->query($query_usuario);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

$tipo_usuario = strtolower($user_info['tipo_usuario']);
$run_usuario = $user_info['run_persona'];

$es_socio = false;
if (strpos($tipo_usuario, 'socio') !== false) {
    $es_socio = true;
}

# obtenemos la lista de eventos
if ($es_socio) {
    $query_eventos = "
        SELECT e.codigo_evento, e.nombre AS nombre_evento, e.fecha_evento, e.tipo_cliente, e.identificador_cliente, 
               l.nombre AS nombre_lugar, s.nombre AS nombre_sucursal
        FROM evento e
        LEFT JOIN lugar l ON e.codigo_lugar = l.codigo_lugar
        LEFT JOIN sucursal s ON e.codigo_sucursal = s.codigo_sucursal
        WHERE e.identificador_cliente = '" . $run_usuario . "'
        ORDER BY e.fecha_evento DESC
    ";
} else {
    $query_eventos = "
        SELECT e.codigo_evento, e.nombre AS nombre_evento, e.fecha_evento, e.tipo_cliente, e.identificador_cliente, 
               l.nombre AS nombre_lugar, s.nombre AS nombre_sucursal
        FROM evento e
        LEFT JOIN lugar l ON e.codigo_lugar = l.codigo_lugar
        LEFT JOIN sucursal s ON e.codigo_sucursal = s.codigo_sucursal
        ORDER BY e.fecha_evento DESC
    ";
}
$stmt_eventos = $db->query($query_eventos);
$eventos = $stmt_eventos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DCColo - Ver Eventos</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>DCColo - Historial de Eventos</h1>
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

    <h2>Eventos Registrados</h2>

    <?php if (count($eventos) == 0) { ?>
        <p>No se encontraron eventos.</p>
    <?php } else { ?>
        <table>
            <tr>
                <th>Codigo</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Sucursal</th>
                <th>Lugar</th>
                <th>Tipo Cliente</th>
                <th>Identificador</th>
            </tr>
            <?php foreach ($eventos as $evt) { ?>
                <tr>
                    <td><?php echo $evt['codigo_evento']; ?></td>
                    <td><?php echo $evt['nombre_evento']; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($evt['fecha_evento'])); ?></td>
                    <td><?php echo $evt['nombre_sucursal']; ?></td>
                    <td><?php echo $evt['nombre_lugar']; ?></td>
                    <td><?php echo $evt['tipo_cliente']; ?></td>
                    <td><?php echo $evt['identificador_cliente']; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <p><a href="crear_evento.php">Crear nuevo evento</a></p>
</body>
</html>

