<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require 'utils.php';
$db = conectarDB();

# obtenemos los datos del formulario
$run             = trim($_POST['run'] ?? '');
$email_login     = trim($_POST['email_login'] ?? '');
$clave           = trim($_POST['clave'] ?? '');
$tipo_usuario    = trim($_POST['tipo_usuario'] ?? '');
$id_cargo        = trim($_POST['id_cargo'] ?? '');
$codigo_sucursal = trim($_POST['codigo_sucursal'] ?? '');
$fecha_inicio    = trim($_POST['fecha_inicio'] ?? '');
$fecha_termino   = trim($_POST['fecha_termino'] ?? '') ?: null;
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$email_personal  = trim($_POST['email_personal'] ?? '') ?: null;
$telefono_cel    = trim($_POST['telefono_celular'] ?? '') ?: null;
$telefono_alt    = trim($_POST['telefono_alternativo'] ?? '') ?: null;
$fecha_nac       = trim($_POST['fecha_nacimiento'] ?? '') ?: null;
$direccion_calle = trim($_POST['direccion_calle'] ?? '') ?: null;
$codigo_comuna   = trim($_POST['codigo_comuna'] ?? '') ?: null;

try {
    # iniciamos transaccion
    $db->beginTransaction();

    #  insertamos en Persona (todos sus atributos)
    $stmt1 = $db->prepare("INSERT INTO persona
        (run, nombre_completo, email, telefono_celular, telefono_alternativo, direccion_calle, codigo_comuna, fecha_nacimiento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->execute([
        $run,
        $nombre_completo,
        $email_personal,
        $telefono_cel,
        $telefono_alt,
        $direccion_calle,
        $codigo_comuna,
        $fecha_nac
    ]);

    # insertamos en usuario
    $stmt2 = $db->prepare("INSERT INTO usuario
        (run_persona, email_login, clave_encriptada, tipo_usuario)
        VALUES (?, ?, ?, ?)");
    $stmt2->execute([$run, $email_login, $clave, $tipo_usuario]);

    # insertamos en Persona_Cargo (información laboral)
    $stmt3 = $db->prepare("INSERT INTO persona_cargo
        (run_persona, id_cargo, codigo_sucursal, fecha_inicio, fecha_termino)
        VALUES (?, ?, ?, ?, ?)");
    $stmt3->execute([
        $run,
        $id_cargo ?: null,
        $codigo_sucursal ?: null,
        $fecha_inicio,
        $fecha_termino
    ]);

    # confirmamos transaccion
    $db->commit();

    # redirigimos con mensaje de exito
    header("Location: registrar_administrativo.php?msg=Usuario+registrado+correctamente");
    exit;

} catch (PDOException $e) {
    # Revertir cambios si hay error como por ejemplo  RUN duplicado
    $db->rollBack();
    header("Location: registrar_administrativo.php?error=" . urlencode("Usuario no se puede registrar: " . $e->getMessage()));
    exit;
}
?>