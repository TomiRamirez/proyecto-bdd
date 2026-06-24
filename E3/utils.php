<?php
function conectarDB() {
    $host = 'stonebraker.ing.uc.cl'; // Cambiar al servidor stonebraker.ing.uc.cl si se quiere usar el servidor remoto
    $dbname = 'tgramirez.e3'; // usuariouc.e3
    $usuario = 'tgramirez.e3'; // usuariouc.e3
    $clave = '23200308'; // Número de alumno

    try {
        $db = new PDO("pgsql:host=$host;dbname=$dbname", $usuario, $clave);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        exit();
    }
}
?>
