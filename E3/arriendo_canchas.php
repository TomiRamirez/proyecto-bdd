<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario_actual = $_SESSION['usuario'];

require_once 'utils.php';
$db = conectarDB();

# obtenemos los datos del usuario
$stmt = $db->prepare("
    SELECT u.run_persona, u.tipo_usuario, p.nombre_completo 
    FROM usuario u 
    LEFT JOIN persona p ON u.run_persona = p.run 
    WHERE u.email_login = ?
");
$stmt->execute([$usuario_actual]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

$run_usuario = $user_info['run_persona'];
$tipo_usuario = strtolower($user_info['tipo_usuario']);

$es_socio = false;
if ($tipo_usuario == 'socio' || $tipo_usuario == 'socio titular' || $tipo_usuario == 'socio_titular') {
    $es_socio = true;
}

# vemos qu eel usuario tenga permiso
if ($es_socio == false && $tipo_usuario != 'administrativo' && $tipo_usuario != 'administrador') {
    die("Acceso denegado. Solo Administrativos, Administradores y Socios pueden arrendar canchas.");
}
# obtenemos la lista de cachas con su sucursal
$stmt_canchas = $db->query("
    SELECT l.codigo_lugar, l.nombre AS nombre_cancha, s.nombre AS nombre_sucursal 
    FROM lugar l
    JOIN sucursal s ON l.codigo_sucursal = s.codigo_sucursal
    WHERE LOWER(l.tipo_lugar) LIKE '%cancha%' OR LOWER(l.nombre) LIKE '%cancha%' 
    ORDER BY s.nombre ASC, l.nombre ASC
");
$canchas = $stmt_canchas->fetchAll(PDO::FETCH_ASSOC);

# obtenemos los socios activos (si no es titular)
$socios_titulares = [];
if ($es_socio == false) {
    # se buscan los socios titulares
    $sql_socios = "
        SELECT p.run, p.nombre_completo 
        FROM socio s
        JOIN persona p ON s.run_persona = p.run
        WHERE LOWER(s.tipo_socio) = 'socio_titular'
        ORDER BY p.nombre_completo ASC
    ";
    $stmt_socios = $db->query($sql_socios);
    $socios_titulares = $stmt_socios->fetchAll(PDO::FETCH_ASSOC);
}

$mensaje = '';
$error = '';

# procesamos el POST del arriendo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar'])) {
    $codigo_lugar = $_POST['codigo_lugar'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    
    $run_reservante = '';
    if ($es_socio == true) {
        $run_reservante = $run_usuario;
    } else {
        $run_reservante = $_POST['run_socio'];
    }
    
    $fecha_inicio = $fecha . ' ' . $hora;
    # lo hacemos por 1 hora
    $hora_fin = date('H:i:s', strtotime($hora) + 3600);
    $fecha_fin = $fecha . ' ' . $hora_fin;

    # verificamos si ya existe reserva en ese horario
    $check_sql = "SELECT COUNT(*) FROM reserva WHERE codigo_lugar = ? AND (fecha_inicio < CAST(? AS TIMESTAMP) AND fecha_fin > CAST(? AS TIMESTAMP))";
    $stmt_check = $db->prepare($check_sql);
    $stmt_check->execute([$codigo_lugar, $fecha_fin, $fecha_inicio]);
    
    $cantidad_reservas = $stmt_check->fetchColumn();
    $is_reserved = false;
    if ($cantidad_reservas > 0) {
        $is_reserved = true;
    }

    if ($is_reserved) {
        $error = 'La cancha ya se encuentra reservada en ese horario.';
    } else {
        # generamos un codigo de reserva aleatorio
        $codigo_reserva = 'RES-' . rand(1000, 9999) . '-' . rand(10, 99);
        
        $insert_sql = "INSERT INTO reserva (codigo_reserva, codigo_lugar, run_reservante, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, CAST(? AS TIMESTAMP), CAST(? AS TIMESTAMP), 'Reservada')";
        $stmt_insert = $db->prepare($insert_sql);
        try {
            $stmt_insert->execute([$codigo_reserva, $codigo_lugar, $run_reservante, $fecha_inicio, $fecha_fin]);
            $mensaje = 'Arriendo registrado exitosamente.';
        } catch (PDOException $e) {
            $error = 'Error al registrar arriendo: ' . $e->getMessage();
        }
    }
}

# variables para el filtro GET
$selected_cancha = '';
if (isset($_GET['cancha'])) {
    $selected_cancha = $_GET['cancha'];
}

$selected_fecha = date('Y-m-d');
if (isset($_GET['fecha'])) {
    $selected_fecha = $_GET['fecha'];
}

# se busca horarios disponibles
$horas_disponibles = [];
if ($selected_cancha != '' && $selected_fecha != '') {
    $todas_las_horas = [];
    for ($i = 9; $i <= 21; $i++) {
        $todas_las_horas[] = sprintf("%02d:00:00", $i);
    }
    $stmt_reservadas = $db->prepare("SELECT CAST(fecha_inicio AS TIME) as hora_reservada FROM reserva WHERE codigo_lugar = ? AND CAST(fecha_inicio AS DATE) = CAST(? AS DATE)");
    $stmt_reservadas->execute([$selected_cancha, $selected_fecha]);
    $horas_reservadas = $stmt_reservadas->fetchAll(PDO::FETCH_COLUMN);

    foreach ($todas_las_horas as $hora) {
        $hora_encontrada = false;
        foreach ($horas_reservadas as $reservada) {
            if ($hora == $reservada) {
                $hora_encontrada = true;
            }
        }
        if ($hora_encontrada == false) {
            $horas_disponibles[] = $hora;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DCColo - Arriendo Canchas</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>DCColo - Arriendo de Canchas</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if ($tipo_usuario === 'administrativo' || $tipo_usuario === 'administrador') { ?>
            <a href="registrar_administrativo.php">Crear Usuario</a>
            <a href="procesar_login.php?ver=logs">Historial</a>
        <?php } ?>
        <a href="arriendo_canchas.php">Arriendo Canchas</a>
        <a href="mis_arriendos.php">Ver Arriendos</a>
        <a href="crear_evento.php">Crear Evento</a>
        <a href="ver_eventos.php">Ver Eventos</a>
        <a href="logout.php">Cerrar Sesion (<?= htmlspecialchars($usuario_actual) ?>)</a>
    </nav>

    <?php if ($mensaje) { ?>
        <p class="ok"><?= htmlspecialchars($mensaje) ?></p>
    <?php } ?>
    <?php if ($error) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <h2>1. Seleccione Cancha y Fecha</h2>
    <form method="GET" action="arriendo_canchas.php">
        <p>Cancha:
        <select name="cancha" onchange="this.form.submit()">
            <option value="">-- Seleccione Cancha --</option>
            <?php foreach ($canchas as $c) {
                $sel = ($selected_cancha == $c['codigo_lugar']) ? 'selected' : '';
                echo "<option value='" . $c['codigo_lugar'] . "' $sel>" . $c['nombre_cancha'] . " (" . $c['nombre_sucursal'] . ")</option>";
            } ?>
        </select></p>
        <p>Fecha: <input type="date" name="fecha" value="<?= htmlspecialchars($selected_fecha) ?>" onchange="this.form.submit()"></p>
        <noscript><p><button type="submit">Ver Disponibilidad</button></p></noscript>
    </form>

    <?php if ($selected_cancha != '' && $selected_fecha != '') { ?>
        <h2>2. Horarios Disponibles y Confirmacion</h2>
        <form method="POST" action="arriendo_canchas.php?cancha=<?= urlencode($selected_cancha) ?>&fecha=<?= urlencode($selected_fecha) ?>">
            <input type="hidden" name="codigo_lugar" value="<?= htmlspecialchars($selected_cancha) ?>">
            <input type="hidden" name="fecha" value="<?= htmlspecialchars($selected_fecha) ?>">

            <?php if (count($horas_disponibles) == 0) { ?>
                <p>No hay horarios disponibles para esta cancha en la fecha seleccionada.</p>
            <?php } else { ?>
                <p>Horario de Inicio:
                <select name="hora" required>
                    <option value="">-- Seleccione Hora --</option>
                    <?php foreach ($horas_disponibles as $h) { ?>
                        <option value="<?= $h ?>"><?= substr($h, 0, 5) ?></option>
                    <?php } ?>
                </select></p>

                <p>Socio Asociado al Arriendo:
                <?php if ($es_socio == true) { ?>
                    <?php
                    $nombre_a_mostrar = $run_usuario;
                    if (isset($user_info['nombre_completo']) && $user_info['nombre_completo'] != '') {
                        $nombre_a_mostrar = $user_info['nombre_completo'];
                    }
                    ?>
                    <input type="text" value="<?= htmlspecialchars($nombre_a_mostrar) ?>" disabled>
                <?php } else { ?>
                    <select name="run_socio" required>
                        <option value="">-- Seleccione Socio Titular --</option>
                        <?php foreach ($socios_titulares as $s) { ?>
                            <option value="<?= $s['run'] ?>"><?= htmlspecialchars($s['nombre_completo']) ?> (<?= $s['run'] ?>)</option>
                        <?php } ?>
                    </select>
                <?php } ?>
                </p>

                <p><button type="submit" name="reservar">Registrar Arriendo</button></p>
            <?php } ?>
        </form>
    <?php } ?>
</body>
</html>
