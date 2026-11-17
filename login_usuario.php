
<?php
session_name('YSG_CLIENTE');
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: catalogo.php');
    exit;
}

$error = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Yess Sweet Garden</title>

    <link rel="stylesheet" href="css/usuarios.css">
</head>

<body>

<main class="pagina-usuario">

    <section class="tarjeta-usuario">

        <a href="inicio.html" class="volver-tienda">
            ← Volver a la tienda
        </a>

        <div class="usuario-decoracion">♡</div>

        <p class="usuario-sobretitulo">BIENVENIDA DE NUEVO</p>

        <h1>Yess Sweet Garden</h1>

        <p class="usuario-descripcion">
            Inicia sesión para encontrar tus favoritos
            y continuar disfrutando de nuestra colección.
        </p>

        <div class="usuario-separador"></div>

        <h2>Iniciar sesión</h2>

        <p class="usuario-instruccion">
            Introduce los datos de tu cuenta.
        </p>

        <?php if ($error): ?>
            <div class="mensaje error">
                Correo o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <form action="verificar_login_usuario.php" method="POST">

            <div class="campo-usuario">
                <label for="correo">Correo electrónico</label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="tunombre@correo.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="campo-usuario">
                <label for="password">Contraseña</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Tu contraseña"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn-crear-cuenta">
                Iniciar sesión
                <span>♡</span>
            </button>

        </form>

        <div class="usuario-pie">
            <p>¿Todavía no tienes cuenta?</p>

            <a href="registro_usuario.php">
                Crear una cuenta
            </a>
        </div>

        <a href="inicio.html" class="enlace-inicio">
            Continuar explorando la tienda
        </a>

    </section>

</main>

</body>
</html>
