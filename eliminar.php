
<?php
require_once 'conexion.php';

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

// Obtener y validar el ID de la prenda
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: listar.php?error=id_invalido');
    exit;
}

try {
    // Comprobar que la prenda exista
    $consulta = $conexion->prepare(
        "SELECT id FROM prendas WHERE id = :id"
    );
    $consulta->execute([':id' => $id]);

    if (!$consulta->fetch(PDO::FETCH_ASSOC)) {
        header('Location: listar.php?error=no_encontrada');
        exit;
    }

    // Eliminar la prenda usando una consulta preparada
    $eliminar = $conexion->prepare(
        "DELETE FROM prendas WHERE id = :id"
    );

    $eliminar->execute([':id' => $id]);

    header('Location: listar.php?eliminacion=exitosa');
    exit;

} catch (PDOException $e) {
    // Registrar el error sin mostrar detalles de la base de datos
    error_log('Error al eliminar una prenda: ' . $e->getMessage());

    header('Location: listar.php?error=eliminacion');
    exit;
}
?>
