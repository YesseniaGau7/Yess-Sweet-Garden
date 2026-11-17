<?php
session_name('YSG_CLIENTE');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

function escaparCarrito($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$usuarioId = (int) $_SESSION['usuario_id'];

// Buscar el carrito del usuario.
$consultaCarrito = $conexion->prepare(
    "SELECT id FROM carrito WHERE usuario_id = ? LIMIT 1"
);
$consultaCarrito->execute([$usuarioId]);
$carrito = $consultaCarrito->fetch(PDO::FETCH_ASSOC);

$productosCarrito = [];
$total = 0;

if ($carrito) {
    $carritoId = (int)$carrito['id'];

    $consultaProductos = $conexion->prepare(
        "SELECT cd.id AS detalle_id,
                cd.cantidad,
                pt.talla,
                p.id AS prenda_id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.color,
                p.imagen_ruta
         FROM carrito_detalles cd
         INNER JOIN prenda_tallas pt ON pt.id = cd.prenda_talla_id
         INNER JOIN prendas p ON p.id = pt.prenda_id
         WHERE cd.carrito_id = ?
         ORDER BY cd.fecha_agregado DESC"
    );

    $consultaProductos->execute([$carritoId]);
    $productosCarrito = $consultaProductos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productosCarrito as $producto) {
        $total += (float)$producto['precio'] * (int)$producto['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi carrito | Yess Sweet Garden</title>
    <link rel="stylesheet" href="css/estilos-catalogo.css">

    <style>
        .pagina-carrito {
            max-width: 1050px;
            margin: 45px auto;
            padding: 0 25px;
        }

        .encabezado-carrito {
            text-align: center;
            margin-bottom: 35px;
        }

        .encabezado-carrito h1 {
            color: #542d2b;
            font-family: Georgia, serif;
            font-size: 36px;
        }

        .encabezado-carrito p {
            color: #80645d;
        }

        .producto-carrito {
            display: flex;
            align-items: center;
            gap: 22px;
            padding: 20px;
            margin-bottom: 18px;
            background: white;
            border: 1px solid #eadbd2;
            border-radius: 15px;
        }

        .producto-carrito img {
            width: 125px;
            height: 145px;
            object-fit: cover;
            border-radius: 10px;
        }

        .informacion-carrito {
            flex: 1;
        }

        .informacion-carrito h2 {
            color: #542d2b;
            font-family: Georgia, serif;
            margin-top: 0;
        }

        .informacion-carrito p {
            color: #80645d;
        }

        .precio-carrito {
            color: #ae2d40 !important;
            font-weight: bold;
            font-size: 19px;
        }

        .resumen-carrito {
            margin-top: 30px;
            padding: 25px;
            background: #fff7f0;
            border: 1px solid #eadbd2;
            border-radius: 15px;
            text-align: right;
        }

        .total-carrito {
            color: #ae2d40;
            font-size: 27px;
            font-weight: bold;
        }

        .carrito-vacio {
            text-align: center;
            padding: 45px 20px;
            background: white;
            border-radius: 15px;
            color: #80645d;
        }

        .enlace-catalogo {
            display: inline-block;
            margin-top: 15px;
            color: #ae2d40;
        }

        @media (max-width: 600px) {
            .producto-carrito {
                align-items: flex-start;
                gap: 14px;
                padding: 13px;
            }

            .producto-carrito img {
                width: 95px;
                height: 120px;
            }
        }
        .volver-perfil {
    margin-bottom: 25px;
}

.volver-perfil a {
    display: inline-block;
    color: #ae2d40;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
}

.volver-perfil a:hover {
    text-decoration: underline;
}
.btn-pagar {
    display: block;
    width: 100%;
    max-width: 300px;
    margin: 25px auto 10px;
    padding: 15px;
    border: none;
    border-radius: 30px;
    background: #ae2d40;
    color: white;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    transition: .25s ease;
}

.btn-pagar:hover {
    background: #8e2334;
    transform: translateY(-2px);
}

.nota-pago {
    color: #80645d;
    font-size: 12px;
    text-align: center;
    margin-top: 12px;
}

.mensaje-compra {
    max-width: 800px;
    margin: 20px auto 30px;
    padding: 22px;
    border-radius: 14px;
    text-align: center;
}

.mensaje-compra h2 {
    font-family: Georgia, serif;
}

.mensaje-compra.exitosa {
    background: #edf3e5;
    border: 1px solid #cbd9b9;
    color: #536b40;
}

.mensaje-compra.error {
    background: #fff0ed;
    border: 1px solid #edc7bd;
    color: #ae2d40;
}
    </style>
</head>

<body>

<main class="pagina-carrito">
<?php if (isset($_GET['compra']) && $_GET['compra'] === 'realizada'): ?>

    <div class="mensaje-compra exitosa">
        <h2>¡Compra Realizada! ♡</h2>
        <p>Gracias por comprar en Yess Sweet Garden.</p>
        <p>Tu compra simulada se ha registrado y el inventario ha sido actualizado.</p>
    </div>

<?php elseif (isset($_GET['error']) && $_GET['error'] === 'stock'): ?>

    <div class="mensaje-compra error">
        <h2>Inventario insuficiente</h2>
        <p>Una o más prendas ya no tienen suficientes unidades disponibles. Revisa tu carrito.</p>
    </div>

<?php elseif (isset($_GET['error']) && $_GET['error'] === 'carrito_vacio'): ?>

    <div class="mensaje-compra error">
        <p>Tu carrito está vacío. Agrega alguna prenda antes de continuar.</p>
    </div>

<?php elseif (isset($_GET['error']) && $_GET['error'] === 'procesar'): ?>

    <div class="mensaje-compra error">
        <p>No se pudo completar la compra. Inténtalo nuevamente.</p>
    </div>

<?php endif; ?>
    <header class="encabezado-carrito">
        <div class="volver-perfil">
    <a href="perfil_usuario.php">← Volver a mi perfil</a>
</div>
        <h1>Mi carrito ♡</h1>
        <p>Revisa las prendas que elegiste.</p>
    </header>

    <?php if (count($productosCarrito) > 0): ?>

        <section>

            <?php foreach ($productosCarrito as $producto): ?>

                <?php
                $archivoImagen = basename((string)($producto['imagen_ruta'] ?? ''));
                $rutaImagen = 'imagenes/prendas/' . rawurlencode($archivoImagen);
                $subtotal = (float)$producto['precio'] * (int)$producto['cantidad'];
                ?>

                <article class="producto-carrito">

                    <?php if ($archivoImagen !== ''): ?>
                        <img src="<?= escaparCarrito($rutaImagen) ?>"
                             alt="<?= escaparCarrito($producto['nombre']) ?>">
                    <?php endif; ?>

                    <div class="informacion-carrito">

                        <h2><?= escaparCarrito($producto['nombre']) ?></h2>

                        <p>
                            Talla: <?= escaparCarrito($producto['talla']) ?>
                        </p>

                        <p>
                            Color: <?= escaparCarrito($producto['color']) ?>
                        </p>

                        <p>
                            Cantidad: <?= (int)$producto['cantidad'] ?>
                        </p>

                        <p class="precio-carrito">
                            $<?= number_format($subtotal, 2) ?> MXN
                        </p>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

       <section class="resumen-carrito">

    <p>Subtotal de tu compra:</p>

    <div class="total-carrito">
        $<?= number_format($total, 2) ?> MXN
    </div>

    <form action="procesar_compra.php" method="POST"
          onsubmit="return confirm('¿Deseas realizar esta compra simulada?');">

        <button type="submit" class="btn-pagar">
            Pagar
        </button>

    </form>

    <p class="nota-pago">
        Esta es una simulación de compra. No se realizará ningún cobro real.
    </p>

</section>

    <?php else: ?>

        <div class="carrito-vacio">
            <h2>Tu carrito está vacío ♡</h2>
            <p>Encuentra algo bonito para agregar a tu carrito.</p>

            <a class="enlace-catalogo" href="catalogo.php">
                Ir al catálogo
            </a>
        </div>

    <?php endif; ?>

</main>

</body>
</html>
