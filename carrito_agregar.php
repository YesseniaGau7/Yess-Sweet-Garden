<?php
session_name('YSG_CLIENTE');
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Debes iniciar sesión para agregar productos al carrito.'
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
$talla = trim($_POST['talla'] ?? '');

if (!$prendaId || $prendaId <= 0 || $talla === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Selecciona una prenda y una talla válida.'
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    // Buscar la talla y comprobar su existencia.
    $consultaTalla = $conexion->prepare(
        "SELECT id, stock
         FROM prenda_tallas
         WHERE prenda_id = ? AND talla = ?
         FOR UPDATE"
    );
    $consultaTalla->execute([$prendaId, $talla]);
    $datoTalla = $consultaTalla->fetch(PDO::FETCH_ASSOC);

    if (!$datoTalla || (int)$datoTalla['stock'] <= 0) {
        $conexion->rollBack();

        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'mensaje' => 'Esta talla ya no está disponible.'
        ]);
        exit;
    }

    $prendaTallaId = (int) $datoTalla['id'];
    $stockDisponible = (int) $datoTalla['stock'];

    // Buscar el carrito del usuario.
    $consultaCarrito = $conexion->prepare(
        "SELECT id FROM carrito
         WHERE usuario_id = ?
         LIMIT 1
         FOR UPDATE"
    );
    $consultaCarrito->execute([$usuarioId]);
    $carrito = $consultaCarrito->fetch(PDO::FETCH_ASSOC);

    // Si todavía no tiene carrito, crearlo.
    if (!$carrito) {
        $crearCarrito = $conexion->prepare(
            "INSERT INTO carrito (usuario_id, fecha_creacion)
             VALUES (?, NOW())"
        );
        $crearCarrito->execute([$usuarioId]);

        $carritoId = (int) $conexion->lastInsertId();
    } else {
        $carritoId = (int) $carrito['id'];
    }

    // Comprobar si esa misma talla ya está en el carrito.
    $consultaDetalle = $conexion->prepare(
        "SELECT id, cantidad
         FROM carrito_detalles
         WHERE carrito_id = ? AND prenda_talla_id = ?
         LIMIT 1
         FOR UPDATE"
    );
    $consultaDetalle->execute([$carritoId, $prendaTallaId]);
    $detalle = $consultaDetalle->fetch(PDO::FETCH_ASSOC);

    if ($detalle) {
        $nuevaCantidad = (int)$detalle['cantidad'] + 1;

        if ($nuevaCantidad > $stockDisponible) {
            $conexion->rollBack();

            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'No hay suficiente stock para agregar otra unidad.'
            ]);
            exit;
        }

        $actualizar = $conexion->prepare(
            "UPDATE carrito_detalles
             SET cantidad = ?
             WHERE id = ?"
        );
        $actualizar->execute([$nuevaCantidad, $detalle['id']]);

    } else {
        $insertar = $conexion->prepare(
            "INSERT INTO carrito_detalles
             (carrito_id, prenda_talla_id, cantidad, fecha_agregado)
             VALUES (?, ?, 1, NOW())"
        );
        $insertar->execute([$carritoId, $prendaTallaId]);
    }

    $conexion->commit();

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Prenda agregada al carrito.',
        'redirigir' => 'carrito_usuario.php'
    ]);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se pudo agregar la prenda al carrito.'
    ]);
}
?>