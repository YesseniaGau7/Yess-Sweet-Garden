
<?php
session_name('YSG_CLIENTE');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito_usuario.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

try {
    $conexion->beginTransaction();

    // Buscar el carrito del usuario.
    $consultaCarrito = $conexion->prepare(
        "SELECT id FROM carrito
         WHERE usuario_id = ?
         LIMIT 1 FOR UPDATE"
    );
    $consultaCarrito->execute([$usuarioId]);
    $carrito = $consultaCarrito->fetch(PDO::FETCH_ASSOC);

    if (!$carrito) {
        throw new Exception('carrito_vacio');
    }

    $carritoId = (int)$carrito['id'];

    // Obtener los productos y bloquear sus existencias.
    $consultaProductos = $conexion->prepare(
        "SELECT cd.id AS detalle_id,
                cd.cantidad,
                pt.id AS prenda_talla_id,
                pt.prenda_id,
                pt.talla,
                pt.stock,
                p.nombre,
                p.precio,
                p.imagen_ruta
         FROM carrito_detalles cd
         INNER JOIN prenda_tallas pt
             ON pt.id = cd.prenda_talla_id
         INNER JOIN prendas p
             ON p.id = pt.prenda_id
         WHERE cd.carrito_id = ?
         FOR UPDATE"
    );

    $consultaProductos->execute([$carritoId]);
    $productos = $consultaProductos->fetchAll(PDO::FETCH_ASSOC);

    if (empty($productos)) {
        throw new Exception('carrito_vacio');
    }

    // Comprobar existencias y calcular el total.
    $total = 0;

    foreach ($productos as $producto) {
        $cantidad = (int)$producto['cantidad'];
        $stock = (int)$producto['stock'];

        if ($cantidad <= 0 || $stock < $cantidad) {
            throw new Exception('stock');
        }

        $total += (float)$producto['precio'] * $cantidad;
    }

    // Registrar la compra.
    $crearCompra = $conexion->prepare(
        "INSERT INTO compras (usuario_id, total, estado)
         VALUES (?, ?, 'Completada')"
    );

    $crearCompra->execute([$usuarioId, $total]);

    $compraId = (int)$conexion->lastInsertId();

    // Preparar el registro de cada producto comprado.
    $insertarDetalle = $conexion->prepare(
        "INSERT INTO compra_detalles
         (compra_id, prenda_id, nombre_prenda, talla,
          cantidad, precio, imagen_ruta)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    // Preparar el descuento de inventario.
    $descontarStock = $conexion->prepare(
        "UPDATE prenda_tallas
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?"
    );

    $descuentoPorPrenda = [];

    foreach ($productos as $producto) {
        $cantidad = (int)$producto['cantidad'];
        $prendaId = (int)$producto['prenda_id'];
        $tallaId = (int)$producto['prenda_talla_id'];

        // Guardar los datos de la compra.
        $insertarDetalle->execute([
            $compraId,
            $prendaId,
            $producto['nombre'],
            $producto['talla'],
            $cantidad,
            $producto['precio'],
            $producto['imagen_ruta']
        ]);

        // Descontar las unidades de esa talla.
        $descontarStock->execute([
            $cantidad,
            $tallaId,
            $cantidad
        ]);

        if ($descontarStock->rowCount() !== 1) {
            throw new Exception('stock');
        }

        if (!isset($descuentoPorPrenda[$prendaId])) {
            $descuentoPorPrenda[$prendaId] = 0;
        }

        $descuentoPorPrenda[$prendaId] += $cantidad;
    }

    // Actualizar el inventario general de cada prenda.
    $actualizarStockGeneral = $conexion->prepare(
        "UPDATE prendas
         SET stock = GREATEST(stock - ?, 0)
         WHERE id = ?"
    );

    foreach ($descuentoPorPrenda as $prendaId => $cantidad) {
        $actualizarStockGeneral->execute([
            $cantidad,
            $prendaId
        ]);
    }

    // Vaciar el carrito.
    $vaciarCarrito = $conexion->prepare(
        "DELETE FROM carrito_detalles
         WHERE carrito_id = ?"
    );

    $vaciarCarrito->execute([$carritoId]);

    // Confirmar todos los cambios.
    $conexion->commit();

    header('Location: carrito_usuario.php?compra=realizada');
    exit;

} catch (Throwable $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    if ($e->getMessage() === 'stock') {
        header('Location: carrito_usuario.php?error=stock');
    } elseif ($e->getMessage() === 'carrito_vacio') {
        header('Location: carrito_usuario.php?error=carrito_vacio');
    } else {
        header('Location: carrito_usuario.php?error=procesar');
    }

    exit;
}
?>
