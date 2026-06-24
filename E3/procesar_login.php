<?php
session_start();
require 'utils.php';

# para ver el historial de logs
if (isset($_GET['ver']) && $_GET['ver'] == 'logs') {
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <title>Historial de Accesos</title>";
    echo "    <link rel='stylesheet' href='estilo.css'>";
    echo "</head>";
    echo "<body>";
    echo "    <div class='dashboard-layout'>";
    echo "        <main class='main-content' style='margin: 40px auto; max-width: 800px;'>";
    echo "            <div class='header-panel' style='text-align:left; margin-bottom: 2rem;'>";
    echo "                <h1>Historial de gente que se ha conectado o fallado</h1>";
    echo "            </div>";
    
    if (file_exists("accesos.log")) {
        $contenido = file_get_contents("accesos.log");
        echo "            <pre style='background: #f4f4f4; padding: 20px; border-radius: 8px; border: 1px solid #ddd; overflow-x: auto; font-family: monospace; font-size: 14px;'>" . htmlspecialchars($contenido) . "</pre>";
    } else {
        echo "            <p>Todavía no hay registros de logs.</p>";
    }
    
    echo "            <div style='margin-top: 2rem;'>";
    echo "                <a href='index.php' class='btn-black' style='display: inline-block; text-decoration: none; text-align: center; width: auto; padding: 10px 20px;'>Volver</a>";
    echo "            </div>";
    echo "        </main>";
    echo "    </div>";
    echo "</body>";
    echo "</html>";
    exit;
}

$db = conectarDB();

$usuario_input = trim($_POST['usuario'] ?? '');
$password_input = trim($_POST['password'] ?? '');
$fecha_hora = date('d-m-Y H:i:s');

try {
    # validamos el  usuario y contraseña junto con sus roles
    # Personas tipo Administrativo, Administrador y Socio Titular
    $query = "SELECT u.email_login, u.tipo_usuario
              FROM usuario u
              JOIN persona p ON u.run_persona = p.run
              LEFT JOIN socio s ON p.run = s.run_persona
              WHERE u.email_login = :usuario
                AND u.clave_encriptada = :password
                AND (
                  LOWER(u.tipo_usuario) IN ('administrativo', 'administrador')
                  OR (LOWER(u.tipo_usuario) = 'socio' AND LOWER(s.tipo_socio) = 'socio_titular')
                )";

    $stmt = $db->prepare($query);
    $stmt->execute([
        'usuario'  => $usuario_input,
        'password' => $password_input
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        # guardamos en el log 
        $archivo = fopen("accesos.log", "a");
        fwrite($archivo, "El dia y hora " . $fecha_hora . " el usuario " . $usuario_input . " logró conectarse exitosamente.\n");
        fclose($archivo);

        # Guardamos sesion y redirigir a pagina principal
        $_SESSION['usuario'] = $user['email_login'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
        header("Location: index.php");
        exit;
    } else {
        # guardamos en el log 
        $archivo = fopen("accesos.log", "a");
        fwrite($archivo, "El dia y hora " . $fecha_hora . " alguien falló al intentar conectarse con el usuario: " . $usuario_input . ".\n");
        fclose($archivo);

        # retornamos al login con mensaje de error
        header("Location: login.php?error=Usuario+o+Clave+err%C3%B3nea");
        exit;
    }
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>