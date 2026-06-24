<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DCColo - Iniciar Sesion</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <h1>Bienvenido a DCColo</h1>
    <hr>

    <h2>Iniciar sesion</h2>

    <p><img src="ronaldo.jpg" alt="Cristiano Ronaldo" style="max-width: 200px;"></p>

    <?php if (!empty($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="procesar_login.php" method="POST">
        <p>Correo electronico: <input type="text" name="usuario" required></p>
        <p>Contrasena: <input type="password" name="password" required></p>
        <p><button type="submit">Entrar</button></p>
    </form>
</body>
</html>