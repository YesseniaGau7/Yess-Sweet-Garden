<?php
session_name('YSG_CLIENTE');
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Debes iniciar sesión para guardar favoritos.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido.'
    ]);
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$prendaId = filter_input(INPUT_POST, 'prenda_id', FILTER_VALIDATE_INT);

if (!$prendaId || $prendaId <= 0) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'La prenda no es válida.'
    ]);
    exit;
}

try {
    // Comprobar que la prenda exista.
    $consultaPrenda = $conexion->prepare(
        "SELECT id FROM prendas WHERE id = ?"
    );
    $consultaPrenda->execute([$prendaId]);

    if (!$consultaPrenda->fetch()) {
        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'No se encontró la prenda.'
        ]);
        exit;
    }

    // Comprobar si ya está guardada en favoritos.
    $consultaFavorito = $conexion->prepare(
        "SELECT id FROM favoritos
         WHERE usuario_id = ? AND prenda_id = ?
         LIMIT 1"
    );
    $consultaFavorito->execute([$usuarioId, $prendaId]);

    $favorito = $consultaFavorito->fetch(PDO::FETCH_ASSOC);

    if ($favorito) {
        // Si ya existe, quitarla de favoritos.
        $eliminar = $conexion->prepare(
            "DELETE FROM favoritos
             WHERE usuario_id = ? AND prenda_id = ?"
        );
        $eliminar->execute([$usuarioId, $prendaId]);

        echo json_encode([
            'ok' => true,
            'favorito' => false,
            'mensaje' => 'Prenda eliminada de favoritos.'
        ]);
    } else {
        // Si no existe, agregarla.
        $insertar = $conexion->prepare(
            "INSERT INTO favoritos (usuario_id, prenda_id)
             VALUES (?, ?)"
        );
        $insertar->execute([$usuarioId, $prendaId]);

        echo json_encode([
            'ok' => true,
            'favorito' => true,
            'mensaje' => 'Prenda agregada a favoritos.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se pudo actualizar favoritos.'
    ]);
}
?>