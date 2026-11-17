
<?php
session_name('YSG_CLIENTE');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'] ?? 'Cliente';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi cuenta | Yess Sweet Garden</title>
    <link rel="stylesheet" href="css/usuarios.css">
</head>

<body>

<main class="pagina-usuario">

    <section class="tarjeta-usuario">

        <a href="catalogo.php" class="volver-tienda">
            ← Volver a la tienda
        </a>

        <div class="usuario-decoracion">♡</div>

        <p class="usuario-sobretitulo">MI ESPACIO PERSONAL</p>

        <h1>¡Hola, <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>!</h1>

        <p class="usuario-descripcion">
            Bienvenida a tu cuenta de Yess Sweet Garden.
            Aquí podrás organizar tus prendas favoritas y tus compras.
        </p>

        <div class="usuario-separador"></div>

        <div class="accesos-perfil">

            <a href="favoritos_usuario.php" class="acceso-perfil">
                <span>♡</span>
                <div>
                    <strong>Mis favoritos</strong>
                    <small>Consulta las prendas que guardaste.</small>
                </div>
            </a>

            <a href="carrito_usuario.php" class="acceso-perfil">
                <span>🛒</span>
                <div>
                    <strong>Mi carrito</strong>
                    <small>Revisa las prendas que deseas comprar.</small>
                </div>
              <a href="mis_compras.php" class="acceso-perfil">

    <span>🛍️</span>

    <div>
        <strong>Mis compras</strong>
        <small>Consulta el historial de tus compras.</small>
    </div>

</a>

        </div>

        <div class="usuario-separador"></div>

        <a href="cerrar_sesion_usuario.php" class="btn-crear-cuenta">
            Cerrar sesión
        </a>

        <a href="inicio.html" class="enlace-inicio">
            Volver al inicio
        </a>

    </section>

</main>

</body>
</html>
