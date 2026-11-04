
<?php
require_once __DIR__ . '/conexion.php';

// Datos de tu cuenta administrativa
$usuario = 'Yess';
$contrasena = 'Yess123';

// Crear hash seguro de la contraseña
$passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);

try {
    $consulta = $conexion->prepare(
        "INSERT INTO administradores (usuario, password)
         VALUES (:usuario, :password)"
    );

    $consulta->execute([
        ':usuario' => $usuario,
        ':password' => $passwordHash
    ]);

    echo "Administrador creado correctamente.";

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "No se pudo crear el administrador. Comprueba si el usuario ya existe.";
}
?>
