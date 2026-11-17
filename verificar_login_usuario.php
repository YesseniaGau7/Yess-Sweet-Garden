
<?php
session_name('YSG_CLIENTE');
session_start();

require_once __DIR__ . '/conexion.php';

// Aceptar únicamente formularios POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_usuario.php');
    exit;
}

$correo = strtolower(trim($_POST['correo'] ?? ''));
$password = $_POST['password'] ?? '';

if ($correo === '' || $password === '') {
    header('Location: login_usuario.php?error=1');
    exit;
}

try {

    $consulta = $conexion->prepare(
        "SELECT id, nombre, correo, password
         FROM usuarios
         WHERE correo = :correo
         LIMIT 1"
    );

    $consulta->execute([
        ':correo' => $correo
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if (
        $usuario &&
        password_verify($password, $usuario['password'])
    ) {

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_correo'] = $usuario['correo'];

        header('Location: catalogo.php?sesion=iniciada');
        exit;
    }

    header('Location: login_usuario.php?error=1');
    exit;

} catch (PDOException $e) {

    error_log('Error en login de cliente: ' . $e->getMessage());

    exit('No fue posible iniciar sesión. Intenta nuevamente.');
}
