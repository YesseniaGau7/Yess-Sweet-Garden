


<?php
require_once __DIR__ . '/sesion.php';
verificarSesion();
require_once 'conexion.php';

try {
    $consulta = $conexion->prepare(
        "SELECT
            p.*,
            COALESCE(t.total_stock, p.stock, 0) AS total_stock,
            COALESCE(t.tallas_resumen, p.talla, '—') AS tallas_resumen
        FROM prendas p

        LEFT JOIN (
            SELECT
                prenda_id,
                SUM(stock) AS total_stock,
                GROUP_CONCAT(
                    CONCAT(talla, ' (', stock, ')')
                    ORDER BY FIELD(talla, 'XS', 'S', 'M', 'L', 'XL', 'Unitalla')
                    SEPARATOR ', '
                ) AS tallas_resumen
            FROM prenda_tallas
            GROUP BY prenda_id
        ) t ON p.id = t.prenda_id

        ORDER BY p.id DESC"
    );

    $consulta->execute();
    $prendas = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error al consultar prendas: ' . $e->getMessage());
    die('Ocurrió un error al consultar las prendas.');
}

function escapar($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$totalPrendas = count($prendas);
$totalStock = 0;
$stockBajo = 0;

foreach ($prendas as $prenda) {
    $stock = (int) $prenda['total_stock'];
    $temporada = (string) $prenda['temporada'];

    $totalStock += $stock;

    if ($stock <= 2) {
        $stockBajo++;
    }

}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventario de prendas | Yess Sweet Garden</title>

 <link rel="stylesheet" href="css/listar.css">
</head>

<body>

    <header class="admin-header">
        <div class="header-contenido">

            <a href="index.php" class="marca">
                <span class="marca-icono">✿</span>

                <span>
                    <strong>Yess Sweet Garden</strong>
                    <small>Panel de administración</small>
                </span>
            </a>
<a href="cerrar_sesion.php" class="btn-cerrar-sesion">
    Cerrar sesión ↗
</a>
            <a href="index.php" class="btn-volver">
                ← Volver al panel
            </a>

        </div>
    </header>

    <main class="contenedor">

        <section class="introduccion">
            <p class="sobre-titulo">CONTROL DE INVENTARIO</p>

            <h1>Prendas registradas</h1>

            <p>
                Consulta, organiza y administra las prendas de tu pequeño jardín.
            </p>
        </section>

        <?php if (
            isset($_GET['eliminacion']) &&
            $_GET['eliminacion'] === 'exitosa'
        ): ?>
            <div class="mensaje mensaje-exito" role="status">
                <span>✓</span>
                La prenda se eliminó correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'id_invalido'): ?>
                <div class="mensaje mensaje-error" role="alert">
                    El identificador de la prenda no es válido.
                </div>
            <?php elseif ($_GET['error'] === 'no_encontrada'): ?>
                <div class="mensaje mensaje-error" role="alert">
                    La prenda que intentas eliminar no existe.
                </div>
            <?php elseif ($_GET['error'] === 'eliminacion'): ?>
                <div class="mensaje mensaje-error" role="alert">
                    No fue posible eliminar la prenda.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- RESUMEN DEL INVENTARIO -->
        <section class="resumen">

            <article class="resumen-card resumen-rosa">
                <div class="resumen-icono">♧</div>

                <div>
                    <p>Prendas registradas</p>
                    <strong><?= $totalPrendas ?></strong>
                    <small>Registros en el catálogo</small>
                </div>
            </article>

            <article class="resumen-card resumen-verde">
                <div class="resumen-icono">▤</div>

                <div>
                    <p>Unidades disponibles</p>
                    <strong><?= $totalStock ?></strong>
                    <small>Suma de las cantidades registradas</small>
                </div>
            </article>

            <article class="resumen-card resumen-crema">
                <div class="resumen-icono">♡</div>

                <div>
                    <p>Stock bajo o agotado</p>
                    <strong><?= $stockBajo ?></strong>
                    <small>Prendas con 2 unidades o menos</small>
                </div>
            </article>

        </section>

        <!-- TABLA -->
        <section class="tabla-card">

            <div class="tabla-encabezado">
                <div>
                    <h2>Inventario de prendas</h2>
                    <p>Consulta los detalles y administra cada registro.</p>
                </div>

                <a href="index.php" class="btn-agregar">
                    + Registrar prenda
                </a>
            </div>

            <?php if (count($prendas) > 0): ?>

                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Prenda</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Temporada</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Stock</th>
                                <th>Fecha de registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($prendas as $prenda): ?>
    <?php

        $stock = (int) $prenda['total_stock'];
        $temporada = (string) $prenda['temporada'];

        $claseTemporada = match ($temporada) {
            'Primavera' => 'primavera',
            'Verano' => 'verano',
            'Otoño' => 'otono',
            'Invierno' => 'invierno',
            default => 'otra-temporada'
        };
    ?>

                                <tr>
                                    <td class="id-celda">
                                        #<?= (int) $prenda['id'] ?>
                                    </td>

                                    <td class="nombre-celda">
                                        <?= escapar($prenda['nombre']) ?>
                                    </td>

                                    <td class="descripcion-celda">
                                        <?= escapar($prenda['descripcion']) ?: 'Sin descripción' ?>
                                    </td>

                                    <td>
                                        <?= escapar($prenda['categoria']) ?>
                                    </td>

                                    <td class="precio-celda">
                                        $<?= number_format(
                                            (float) $prenda['precio'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="etiqueta-temporada <?= $claseTemporada ?>">
                                            <?= escapar($temporada) ?>
                                        </span>
                                    </td>

                                    <td class="tallas-celda">
    <?= escapar($prenda['tallas_resumen']) ?: '—' ?>
</td>

                                    <td>
                                        <?= escapar($prenda['color']) ?: '—' ?>
                                    </td>

                                    <td>
                                        <span class="etiqueta-stock <?= $stock === 0 ? 'agotado' : ($stock <= 2 ? 'bajo' : 'disponible') ?>">
                                            <?= $stock ?>
                                            <?= $stock === 0 ? ' · Agotado' : '' ?>
                                        </span>
                                    </td>

                                    <td class="fecha-celda">
                                        <?= escapar($prenda['fecha_registro']) ?>
                                    </td>

                                    <td>
                                        <div class="acciones">

                                            <a
                                                class="btn-editar"
                                                href="editar.php?id=<?= (int) $prenda['id'] ?>"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                action="eliminar.php"
                                                method="POST"
                                                onsubmit="return confirm('¿Estás segura de que deseas eliminar esta prenda?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $prenda['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-eliminar"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tabla-pie">
                    Mostrando <?= $totalPrendas ?>
                    <?= $totalPrendas === 1 ? 'prenda registrada' : 'prendas registradas' ?>.
                </div>

            <?php else: ?>

                <div class="estado-vacio">
                    <span>✿</span>
                    <h3>Tu inventario está esperando nuevas prendas</h3>
                    <p>Cuando registres una prenda, aparecerá aquí.</p>

                    <a href="index.php" class="btn-agregar">
                        Registrar primera prenda
                    </a>
                </div>

            <?php endif; ?>

        </section>

        <div class="nota-inferior">
            <span>♡</span>
            Mantén actualizadas las cantidades para llevar un mejor control de tu inventario.
        </div>

    </main>

    <footer class="admin-footer">
        <strong>✿ Yess Sweet Garden</strong>
        <span>Administración interna · 2026</span>
    </footer>

</body>
</html>
