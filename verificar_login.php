
<?php
session_start();

require_once __DIR__ . '/conexion.php';

// Verificar que el formulario se haya enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Obtener los datos del formulario
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($usuario === '' || $contrasena === '') {
    header('Location: login.php?error=1');
    exit;
}

try {
    // Buscar al administrador en la base de datos
    $consulta = $conexion->prepare(
        "SELECT id, usuario, password
         FROM administradores
         WHERE usuario = :usuario
         LIMIT 1"
    );

    $consulta->execute([
        ':usuario' => $usuario
    ]);

    $administrador = $consulta->fetch(PDO::FETCH_ASSOC);

    // Comprobar la contraseña
    if (
        $administrador &&
        password_verify($contrasena, $administrador['password'])
    ) {
        // Crear la sesión administrativa
        session_regenerate_id(true);

        $_SESSION['administrador'] = $administrador['usuario'];
        $_SESSION['administrador_id'] = $administrador['id'];

        header('Location: index.php');
        exit;
    }

    // Credenciales incorrectas
    header('Location: login.php?error=1');
    exit;

} catch (PDOException $e) {
    error_log('Error al verificar el login: ' . $e->getMessage());
    die('Ocurrió un error al intentar iniciar sesión.');
}
?>
