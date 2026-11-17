
<?php
session_name('YSG_CLIENTE');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

function escaparCompra($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$usuarioId = (int)$_SESSION['usuario_id'];

// Consultar las compras del usuario y sus productos.
$consulta = $conexion->prepare(
    "SELECT c.id AS compra_id,
            c.total,
            c.fecha_compra,
            c.estado,
            cd.nombre_prenda,
            cd.talla,
            cd.cantidad,
            cd.precio,
            cd.imagen_ruta
     FROM compras c
     INNER JOIN compra_detalles cd
         ON cd.compra_id = c.id
     WHERE c.usuario_id = ?
     ORDER BY c.fecha_compra DESC, c.id DESC, cd.id ASC"
);

$consulta->execute([$usuarioId]);
$resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Agrupar los productos por compra.
$compras = [];

foreach ($resultados as $fila) {
    $compraId = $fila['compra_id'];

    if (!isset($compras[$compraId])) {
        $compras[$compraId] = [
            'id' => $compraId,
            'total' => $fila['total'],
            'fecha' => $fila['fecha_compra'],
            'estado' => $fila['estado'],
            'productos' => []
        ];
    }

    $compras[$compraId]['productos'][] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis compras | Yess Sweet Garden</title>

    <link rel="stylesheet" href="css/estilos-catalogo.css">

    <style>
        .pagina-compras {
            max-width: 1050px;
            margin: 35px auto 55px;
            padding: 0 25px;
        }

        .volver-perfil {
            margin-bottom: 25px;
        }

        .volver-perfil a {
            color: #ae2d40;
            text-decoration: none;
            font-weight: 600;
        }

        .volver-perfil a:hover {
            text-decoration: underline;
        }

        .encabezado-compras {
            text-align: center;
            margin-bottom: 35px;
        }

        .encabezado-compras h1 {
            color: #542d2b;
            font-family: Georgia, serif;
            font-size: 36px;
        }

        .encabezado-compras p {
            color: #80645d;
        }

        .tarjeta-compra {
            background: white;
            border: 1px solid #eadbd2;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .datos-compra {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eadbd2;
        }

        .datos-compra h2 {
            color: #542d2b;
            font-family: Georgia, serif;
            font-size: 22px;
            margin: 0;
        }

        .datos-compra p {
            color: #80645d;
            font-size: 14px;
            margin: 6px 0;
        }

        .estado-compra {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            background: #e8f0df;
            color: #536b40;
            font-size: 13px;
        }

        .producto-comprado {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 15px 0;
            border-bottom: 1px solid #f0e5dd;
        }

        .producto-comprado:last-child {
            border-bottom: none;
        }

        .producto-comprado img {
            width: 95px;
            height: 110px;
            object-fit: contain;
            background: #fffaf5;
            border-radius: 10px;
        }

        .info-producto-comprado {
            flex: 1;
        }

        .info-producto-comprado h3 {
            color: #542d2b;
            font-family: Georgia, serif;
            margin: 0 0 8px;
        }

        .info-producto-comprado p {
            color: #80645d;
            margin: 5px 0;
            font-size: 14px;
        }

        .total-compra {
            text-align: right;
            color: #ae2d40;
            font-size: 23px;
            font-weight: bold;
            padding-top: 18px;
        }

        .sin-compras {
            text-align: center;
            padding: 45px 20px;
            background: white;
            border: 1px solid #eadbd2;
            border-radius: 15px;
            color: #80645d;
        }

        .sin-compras a {
            display: inline-block;
            margin-top: 15px;
            color: #ae2d40;
        }

        @media (max-width: 550px) {
            .producto-comprado img {
                width: 75px;
                height: 90px;
            }

            .tarjeta-compra {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<main class="pagina-compras">

    <div class="volver-perfil">
        <a href="perfil_usuario.php">← Volver a mi perfil</a>
    </div>

    <header class="encabezado-compras">
        <h1>Mis compras ♡</h1>
        <p>Un pequeño historial de tus compras en nuestro jardín.</p>
    </header>

    <?php if (!empty($compras)): ?>

        <?php foreach ($compras as $compra): ?>

            <section class="tarjeta-compra">

                <div class="datos-compra">

                    <div>
                        <h2>Compra #<?= (int)$compra['id'] ?></h2>

                        <p>
                            Fecha:
                            <?= date('d/m/Y H:i', strtotime($compra['fecha'])) ?>
                        </p>
                    </div>

                    <span class="estado-compra">
                        <?= escaparCompra($compra['estado']) ?>
                    </span>

                </div>

                <?php foreach ($compra['productos'] as $producto): ?>

                    <?php
                    $archivoImagen = basename(
                        (string)($producto['imagen_ruta'] ?? '')
                    );

                    $rutaImagen = 'imagenes/prendas/' .
                        rawurlencode($archivoImagen);

                    $subtotal = (float)$producto['precio'] *
                        (int)$producto['cantidad'];
                    ?>

                    <article class="producto-comprado">

                        <?php if ($archivoImagen !== ''): ?>

                            <img
                                src="<?= escaparCompra($rutaImagen) ?>"
                                alt="<?= escaparCompra($producto['nombre_prenda']) ?>"
                            >

                        <?php endif; ?>

                        <div class="info-producto-comprado">

                            <h3>
                                <?= escaparCompra($producto['nombre_prenda']) ?>
                            </h3>

                            <p>
                                Talla: <?= escaparCompra($producto['talla']) ?>
                            </p>

                            <p>
                                Cantidad: <?= (int)$producto['cantidad'] ?>
                            </p>

                            <p>
                                Precio unitario:
                                $<?= number_format((float)$producto['precio'], 2) ?> MXN
                            </p>

                            <p>
                                Subtotal:
                                $<?= number_format($subtotal, 2) ?> MXN
                            </p>

                        </div>

                    </article>

                <?php endforeach; ?>

                <div class="total-compra">
                    Total:
                    $<?= number_format((float)$compra['total'], 2) ?> MXN
                </div>

            </section>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="sin-compras">
            <h2>Aún no tienes compras ♡</h2>

            <p>
                Cuando realices una compra, aparecerá aquí tu historial.
            </p>

            <a href="catalogo.php">Explorar catálogo</a>
        </div>

    <?php endif; ?>

</main>

</body>
</html>
