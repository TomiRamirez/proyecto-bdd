<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once 'utils.php';
$db = conectarDB();

$usuario_actual = $_SESSION['usuario'];
$query_usuario = "SELECT tipo_usuario FROM usuario WHERE email_login = '" . $usuario_actual . "'";
$stmt = $db->query($query_usuario);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

$tipo_usuario = strtolower($user_info['tipo_usuario']);
$es_socio = false;
if (strpos($tipo_usuario, 'socio') !== false) {
    $es_socio = true;
}

if (!$es_socio && $tipo_usuario != 'administrativo' && $tipo_usuario != 'administrador') {
    echo "Acceso denegado.";
    exit;
}

$query_lugares = "SELECT l.codigo_lugar, l.nombre AS lugar, s.codigo_sucursal, s.nombre AS sucursal FROM lugar l JOIN sucursal s ON l.codigo_sucursal = s.codigo_sucursal ORDER BY s.nombre, l.nombre";
$lugares = $db->query($query_lugares)->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_evento = 'EVT-' . rand(1000, 9999);
    $nombre = $_POST['nombre_evento'];
    $fecha = $_POST['fecha_evento'];
    
    # Separamos el valor del lugar que viene como "LUG-1|SUC-1"
    $lugar_sucursal = explode("|", $_POST['lugar_sucursal']);
    $lugar = $lugar_sucursal[0];
    $sucursal = $lugar_sucursal[1];
    
    $tipo_cliente = $_POST['tipo_cliente'];
    $identificador = $_POST['identificador'];
    $valor = $_POST['valor'];
    $medio_pago = $_POST['medio_pago'];

    $check_sql = "SELECT COUNT(*) FROM evento WHERE codigo_lugar = '" . $lugar . "' AND fecha_evento = '" . $fecha . "'";
    $ocupado = $db->query($check_sql)->fetchColumn();

    if ($ocupado > 0) {
        $error = "El lugar ya está ocupado esa fecha.";
    } else {
        # guardamos el eevento
        $sql1 = "INSERT INTO evento (codigo_evento, nombre, fecha_evento, codigo_lugar, codigo_sucursal, tipo_cliente, identificador_cliente) VALUES ('" . $codigo_evento . "', '" . $nombre . "', '" . $fecha . "', '" . $lugar . "', '" . $sucursal . "', '" . $tipo_cliente . "', '" . $identificador . "')";
        $db->query($sql1);

        # guardamos el pago
        $fecha_pago = date('Y-m-d');
        $sql2 = "INSERT INTO pago_evento (codigo_evento, fecha_pago, monto, tipo_pago) VALUES ('" . $codigo_evento . "', '" . $fecha_pago . "', " . $valor . ", '" . $medio_pago . "')";
        $db->query($sql2);

        # guardamos los invitados (3) 
        for ($i = 1; $i <= 3; $i++) {
            $nom_inv = $_POST['inv_nombre_' . $i];
            $run_inv = $_POST['inv_run_' . $i];
            if ($nom_inv != '') {
                $sql3 = "INSERT INTO asistente_evento (codigo_evento, run_asistente, nombre_asistente) VALUES ('" . $codigo_evento . "', '" . $run_inv . "', '" . $nom_inv . "')";
                $db->query($sql3);
            }
        }
        $mensaje = "Evento creado con éxito! Código: " . $codigo_evento;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Evento</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="dashboard-layout">
    <header class="top-nav">
        <a href="index.php" class="logo">DCColo</a>
        <nav class="nav-links">
            <a href="index.php" class="nav-item">Panel Principal</a>
            <a href="arriendo_canchas.php" class="nav-item">Arriendo Canchas</a>
            <a href="mis_arriendos.php" class="nav-item">Ver Arriendos</a>
            <a href="crear_evento.php" class="nav-item" style="font-weight: bold; color: #0d47a1;">Crear Evento</a>
            <a href="ver_eventos.php" class="nav-item">Ver Eventos</a>
            <a href="logout.php" class="nav-item" style="color: #c62828;">Cerrar Sesión</a>
        </nav>
    </header>
    <main class="main-content">
        <h2>Crear Evento</h2>
        <?php if ($mensaje != '') echo "<p style='color:green; padding:10px; background:#d4edda;'>$mensaje</p>"; ?>
        <?php if ($error != '') echo "<p style='color:red; padding:10px; background:#f8d7da;'>$error</p>"; ?>
        
        <form method="POST" action="crear_evento.php" class="form-section">
            <h3 style="margin-top:10px;">1. Datos Básicos</h3>
            <p>Nombre Evento: <input type="text" name="nombre_evento" required></p>
            <p>Fecha: <input type="date" name="fecha_evento" required></p>
            <p>Lugar: 
            <select name="lugar_sucursal" required>
                <option value="">-- Seleccione Sucursal y Lugar --</option>
                <?php foreach ($lugares as $l) { ?>
                    <option value="<?php echo $l['codigo_lugar'] . '|' . $l['codigo_sucursal']; ?>">
                        <?php echo $l['sucursal'] . " - " . $l['lugar']; ?>
                    </option>
                <?php } ?>
            </select></p>

            <h3>2. Cliente</h3>
            <p>Tipo de Cliente: 
            <select name="tipo_cliente" required>
                <option value="socio">Socio</option>
                <option value="empresa">Empresa</option>
            </select></p>
            <p>RUN/RUT del Cliente: <input type="text" name="identificador" placeholder="Sin puntos ni guion" required></p>

            <h3>3. Pago (Primera Cuota)</h3>
            <p>Monto $: <input type="number" name="valor" required></p>
            <p>Medio de pago: 
            <select name="medio_pago" required>
                <option value="efectivo">Efectivo</option>
                <option value="debito">Débito</option>
                <option value="transferencia">Transferencia</option>
            </select></p>

            <h3>4. Lista de Invitados (Opcional, máximo 3 por ahora)</h3>
            <p>Invitado 1 Nombre: <input type="text" name="inv_nombre_1"> RUN: <input type="text" name="inv_run_1"></p>
            <p>Invitado 2 Nombre: <input type="text" name="inv_nombre_2"> RUN: <input type="text" name="inv_run_2"></p>
            <p>Invitado 3 Nombre: <input type="text" name="inv_nombre_3"> RUN: <input type="text" name="inv_run_3"></p>

            <button type="submit" class="btn-black" style="margin-top:15px;">Guardar Evento</button>
        </form>
    </main>
</div>
</body>
</html>
