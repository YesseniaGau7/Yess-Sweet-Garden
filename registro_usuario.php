
<?php
require_once __DIR__ . '/conexion.php';

$mensaje = '';
$tipoMensaje = '';

$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validar campos
    if ($nombre === '' || $correo === '' || $password === '') {

        $mensaje = 'Completa todos los campos.';
        $tipoMensaje = 'error';

    } elseif (mb_strlen($nombre) > 100) {

        $mensaje = 'El nombre no debe superar los 100 caracteres.';
        $tipoMensaje = 'error';

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = 'Introduce un correo electrónico válido.';
        $tipoMensaje = 'error';

    } elseif (mb_strlen($correo) > 150) {

        $mensaje = 'El correo electrónico es demasiado largo.';
        $tipoMensaje = 'error';

    } elseif (strlen($password) < 8) {

        $mensaje = 'La contraseña debe tener al menos 8 caracteres.';
        $tipoMensaje = 'error';

    } else {

        try {

            // Comprobar si el correo ya existe
            $consulta = $conexion->prepare(
                "SELECT id FROM usuarios WHERE correo = :correo LIMIT 1"
            );

            $consulta->execute([
                ':correo' => $correo
            ]);

            if ($consulta->fetch()) {

                $mensaje = 'Este correo ya tiene una cuenta registrada.';
                $tipoMensaje = 'error';

            } else {

                // Cifrar contraseña
                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Registrar usuario
                $insertar = $conexion->prepare(
                    "INSERT INTO usuarios (nombre, correo, password)
                     VALUES (:nombre, :correo, :password)"
                );

                $insertar->execute([
                    ':nombre' => $nombre,
                    ':correo' => $correo,
                    ':password' => $passwordHash
                ]);

                $mensaje = '¡Tu cuenta se creó correctamente! Ya puedes iniciar sesión.';
                $tipoMensaje = 'exito';

                // Limpiar campos
                $nombre = '';
                $correo = '';
            }

        } catch (PDOException $e) {

            error_log('Error al registrar usuario: ' . $e->getMessage());

            $mensaje = 'No fue posible crear tu cuenta. Intenta nuevamente.';
            $tipoMensaje = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear cuenta | Yess Sweet Garden</title>

    <link rel="stylesheet" href="css/usuarios.css">
</head>

<body>

    <main class="pagina-usuario">

        <section class="tarjeta-usuario">

            <a href="inicio.html" class="volver-tienda">
                ← Volver a la tienda
            </a>

            <div class="usuario-decoracion">✿</div>

            <p class="usuario-sobretitulo">
                BIENVENIDA A
            </p>

            <h1>Yess Sweet Garden</h1>

            <p class="usuario-descripcion">
                Crea tu cuenta y guarda tus prendas favoritas
                para tenerlas siempre a tu alcance.
            </p>

            <div class="usuario-separador"></div>

            <h2>Crear una cuenta</h2>

            <p class="usuario-instruccion">
                Completa tus datos para registrarte.
            </p>

            <?php if ($mensaje !== ''): ?>
                <div class="mensaje <?= escaparUsuario($tipoMensaje) ?>">
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="registro_usuario.php" method="POST">

                <div class="campo-usuario">

                    <label for="nombre">Nombre completo</label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Ej. María González"
                        maxlength="100"
                        value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="name"
                        required
                    >

                </div>

                <div class="campo-usuario">

                    <label for="correo">Correo electrónico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="tunombre@correo.com"
                        maxlength="150"
                        value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
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
                        placeholder="Mínimo 8 caracteres"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <small>
                        Utiliza al menos 8 caracteres.
                    </small>

                </div>

                <button type="submit" class="btn-crear-cuenta">
                    Crear mi cuenta
                    <span>♡</span>
                </button>

            </form>

            <div class="usuario-pie">
                <p>¿Ya tienes una cuenta?</p>

                <a href="login_usuario.php">
                    Inicia sesión
                </a>
            </div>

            <a href="inicio.html" class="enlace-inicio">
                Continuar explorando la tienda
            </a>

        </section>

    </main>

</body>
</html>
