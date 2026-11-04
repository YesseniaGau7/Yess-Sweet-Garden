
<?php
session_start();

// Si ya inició sesión, enviarlo al panel
if (isset($_SESSION['administrador'])) {
    header('Location: index.php');
    exit;
}

$error = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Acceso administrativo | Yess Sweet Garden</title>

    <link rel="stylesheet" href="../css/login.css">
</head>

<body>

    <main class="login-contenedor">

        <div class="login-icono">✿</div>

        <p class="login-etiqueta">YESS SWEET GARDEN</p>

        <h1>Bienvenida de nuevo</h1>

        <p class="login-descripcion">
            Ingresa tus datos para administrar tu pequeño jardín.
        </p>

        <?php if ($error): ?>
            <p class="mensaje-error" role="alert">
                Usuario o contraseña incorrectos.
            </p>
        <?php endif; ?>

        <form action="verificar_login.php" method="POST">

            <label for="usuario">Usuario</label>

            <input
                type="text"
                id="usuario"
                name="usuario"
                placeholder="Ingresa tu usuario"
                required
                autocomplete="username"
            >

            <label for="contrasena">Contraseña</label>

            <input
                type="password"
                id="contrasena"
                name="contrasena"
                placeholder="Ingresa tu contraseña"
                required
                autocomplete="current-password"
            >

            <button type="submit">
                Iniciar sesión →
            </button>

        </form>

        <a href="../inicio.html" class="volver">
            ← Volver a la página principal
        </a>

    </main>

</body>
</html>
