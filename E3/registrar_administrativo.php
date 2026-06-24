<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario_actual = $_SESSION['usuario'];

require_once 'utils.php';
$db = conectarDB();

# obtenemos los cargos activos
$cargos_stmt = $db->query("SELECT id_cargo, nombre FROM cargo ORDER BY nombre ASC");
$cargos = $cargos_stmt->fetchAll(PDO::FETCH_ASSOC);

# obtenemos las sucursales activas
$sucursales_stmt = $db->query("SELECT codigo_sucursal, nombre FROM sucursal ORDER BY nombre ASC");
$sucursales = $sucursales_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DCColo - Registrar Administrativo</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Barra lateral -->
        <header class="top-nav">
            <a href="index.php" class="logo">DCColo</a>
            <nav class="nav-links">
                <a href="index.php" class="nav-item">Panel Principal</a>
                <a href="registrar_administrativo.php" class="nav-item">Crear Usuario</a>
                <a href="procesar_login.php?ver=logs" class="nav-item">Historial</a>
                <a href="logout.php" class="nav-item" style="color: #c62828;">Cerrar Sesión (<?= htmlspecialchars($usuario_actual) ?>)</a>
            </nav>
        </header>

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="header-panel" style="text-align:left; margin-bottom:2rem;">
                <h1>Nuevo Perfil Administrativo</h1>
                <p>Complete el formulario para registrar un nuevo administrador o administrativo en el sistema DCColo.</p>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="success-msg" style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:15px; border-radius:8px; margin-bottom:20px;">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_GET['error'])): ?>
                <div class="error-msg" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:15px; border-radius:8px; margin-bottom:20px;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="procesar_registro.php" method="POST">

                <!-- Credenciales de Acceso -->
                <div class="form-section">
                    <h3 class="form-section-title">Credenciales de Acceso</h3>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>RUN (run_persona) *</label>
                            <input type="text" name="run" required>
                        </div>
                        <div class="form-group">
                            <label>Email de Inicio de Sesión (email_login) *</label>
                            <input type="email" name="email_login" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña Encriptada (clave_encriptada) *</label>
                            <input type="password" name="clave" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Usuario (tipo_usuario)</label>
                            <select name="tipo_usuario" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Administrativo">Administrativo</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Información Laboral -->
                <div class="form-section">
                    <h3 class="form-section-title">Información Laboral</h3>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Cargo (id_cargo) *</label>
                            <select name="id_cargo" required>
                                <option value="">-- Seleccione Cargo --</option>
                                <?php foreach ($cargos as $c): ?>
                                    <option value="<?= $c['id_cargo'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sucursal (codigo_sucursal) *</label>
                            <select name="codigo_sucursal" required>
                                <option value="">-- Seleccione Sucursal --</option>
                                <?php foreach ($sucursales as $s): ?>
                                    <option value="<?= $s['codigo_sucursal'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Inicio (fecha_inicio)</label>
                            <input type="date" name="fecha_inicio" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Término (fecha_termino)</label>
                            <input type="date" name="fecha_termino">
                        </div>
                    </div>
                </div>

                <!-- Datos Personales -->
                <div class="form-section">
                    <h3 class="form-section-title">Datos Personales</h3>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Nombre Completo (nombre_completo)</label>
                            <input type="text" name="nombre_completo" required>
                        </div>
                        <div class="form-group">
                            <label>Email Personal (email)</label>
                            <input type="email" name="email_personal">
                        </div>
                        <div class="form-group">
                            <label>Teléfono Celular (telefono_celular)</label>
                            <input type="text" name="telefono_celular">
                        </div>
                        <div class="form-group">
                            <label>Teléfono Alternativo (telefono_alternativo)</label>
                            <input type="text" name="telefono_alternativo">
                        </div>
                        <div class="form-group">
                            <label>Fecha de Nacimiento (fecha_nacimiento)</label>
                            <input type="date" name="fecha_nacimiento">
                        </div>
                        <div class="form-group">
                            <label>Dirección (direccion_calle)</label>
                            <input type="text" name="direccion_calle">
                        </div>
                        <div class="form-group">
                            <label>Código de Comuna (codigo_comuna)</label>
                            <input type="number" name="codigo_comuna">
                        </div>
                    </div>
                </div>

                <div style="text-align:right; margin-top:2rem;">
                    <button type="submit" class="btn-black" style="width:auto; padding: 12px 30px;">
                        Registrar Administrativo
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>