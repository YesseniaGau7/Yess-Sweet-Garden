
<?php
require_once 'conexion.php';

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$temporadas = [
    'Primavera' => [
        'id' => 'primavera',
        'frase' => 'Flores, colores y nuevos comienzos.',
        'descripcion' => 'Prendas ligeras, estampados florales y colores frescos.',
        'simbolo' => '✿',
        'clase' => 'primavera'
    ],
    'Verano' => [
        'id' => 'verano',
        'frase' => 'Días soleados y prendas llenas de vida.',
        'descripcion' => 'Estilos frescos y colores que iluminan cada día.',
        'simbolo' => '☀',
        'clase' => 'verano'
    ],
    'Otoño' => [
        'id' => 'otono',
        'frase' => 'Texturas bonitas y tonos cálidos.',
        'descripcion' => 'Prendas para combinar y disfrutar los días frescos.',
        'simbolo' => '❧',
        'clase' => 'otono'
    ],
    'Invierno' => [
        'id' => 'invierno',
        'frase' => 'Prendas acogedoras para días frescos.',
        'descripcion' => 'Suéteres, abrigos y prendas para el frío.',
        'simbolo' => '❄',
        'clase' => 'invierno'
    ]
];

$temporadaSeleccionada = $_GET['temporada'] ?? null;
$categoriaSeleccionada = $_GET['categoria'] ?? null;

$errorCatalogo = false;
$errorCategoria = false;
$prendas = [];
$categoriasDisponibles = [];

/* Validar la temporada */

if ($temporadaSeleccionada !== null) {
    if (
        !is_string($temporadaSeleccionada) ||
        !array_key_exists($temporadaSeleccionada, $temporadas)
    ) {
        http_response_code(404);
        $errorCatalogo = true;
        $temporadaSeleccionada = null;
        $categoriaSeleccionada = null;
    }
}

/* Consultar las prendas de la temporada */

if ($temporadaSeleccionada !== null) {
    try {
        $consulta = $conexion->prepare(
            "SELECT *
             FROM prendas
             WHERE temporada = :temporada
             ORDER BY id DESC"
        );

        $consulta->execute([
            ':temporada' => $temporadaSeleccionada
        ]);

        $prendas = $consulta->fetchAll(PDO::FETCH_ASSOC);

        /* Obtener las categorías existentes */

        foreach ($prendas as $prenda) {
            $nombreCategoria = trim(
                (string) ($prenda['categoria'] ?? '')
            );

            if ($nombreCategoria !== '') {
                $categoriasDisponibles[$nombreCategoria] = true;
            }
        }

        $categoriasDisponibles = array_keys($categoriasDisponibles);

        natcasesort($categoriasDisponibles);
        $categoriasDisponibles = array_values($categoriasDisponibles);

        /* Filtrar por categoría seleccionada */

        if ($categoriaSeleccionada !== null) {
            if (
                !is_string($categoriaSeleccionada) ||
                !in_array(
                    $categoriaSeleccionada,
                    $categoriasDisponibles,
                    true
                )
            ) {
                $errorCategoria = true;
                $categoriaSeleccionada = null;
            } else {
                $prendas = array_values(
                    array_filter(
                        $prendas,
                        function ($prenda) use ($categoriaSeleccionada) {
                            return $prenda['categoria'] === $categoriaSeleccionada;
                        }
                    )
                );
            }
        }

    } catch (PDOException $e) {
        error_log(
            'Error al consultar el catálogo: ' . $e->getMessage()
        );

        $errorCatalogo = true;
        $prendas = [];
        $categoriasDisponibles = [];
    }
}

/* Título de la página */

if ($temporadaSeleccionada === null) {
    $tituloPagina = 'Catálogo';
} elseif ($categoriaSeleccionada !== null && !$errorCategoria) {
    $tituloPagina = $categoriaSeleccionada . ' - ' . $temporadaSeleccionada;
} else {
    $tituloPagina = $temporadaSeleccionada;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escapar($tituloPagina) ?> | Yess Sweet Garden</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=DM+Sans:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@1,500;1,600&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/estilos-catalogo.css">
</head>

<body>

    <!-- ANUNCIO SUPERIOR -->

    <div class="anuncio">
        ✿ Bienvenida a tu pequeño jardín de moda ✿
    </div>

    <!-- ENCABEZADO -->

    <header class="encabezado">

        <a href="inicio.html" class="logo">
            Yess Sweet Garden
        </a>

        <nav aria-label="Navegación principal">

            <a href="inicio.html">Inicio</a>

            <a
                href="catalogo.php"
                class="<?= $temporadaSeleccionada === null ? 'activo' : '' ?>"
            >
                Catálogo
            </a>

            <a href="inicio.html#colecciones">Colecciones</a>
            <a href="inicio.html#nosotros">Nosotros</a>

        </nav>

    </header>

    <main>

        <?php if ($errorCatalogo): ?>

            <!-- ERROR AL CARGAR EL CATÁLOGO -->

            <section class="mensaje-vacio mensaje-error">

                <span>♡</span>

                <h1>No pudimos cargar el catálogo</h1>

                <p>Intenta nuevamente más tarde.</p>

                <a href="catalogo.php" class="boton-volver">
                    Volver al catálogo
                </a>

            </section>

        <?php elseif ($temporadaSeleccionada === null): ?>

            <!-- NIVEL 1: LAS CUATRO ESTACIONES -->

            <section class="portada-catalogo">

                <span class="etiqueta">
                    NUESTRO PEQUEÑO JARDÍN ✿
                </span>

                <h1>
                    Encuentra tu temporada
                    <span>favorita.</span>
                </h1>

                <p>
                    Cuatro estaciones, muchos estilos y prendas
                    para expresar tu personalidad.
                </p>

            </section>

            <section class="categorias-temporada">

                <div class="introduccion-categorias">

                    <p class="section-label">
                        EXPLORA LAS COLECCIONES
                    </p>

                    <h2>¿Qué temporada va contigo?</h2>

                    <p>
                        Elige una estación para descubrir sus prendas.
                    </p>

                </div>

                <div class="rejilla-categorias">

                    <?php foreach ($temporadas as $nombre => $datos): ?>

                        <a
                            class="tarjeta-categoria <?= escapar($datos['clase']) ?>"
                            href="catalogo.php?temporada=<?= rawurlencode($nombre) ?>"
                        >

                            <div class="categoria-decoracion">
                                <span><?= escapar($datos['simbolo']) ?></span>
                            </div>

                            <div class="categoria-texto">

                                <span class="numero-temporada">
                                    COLECCIÓN · <?= escapar($nombre) ?>
                                </span>

                                <h2><?= escapar($nombre) ?></h2>

                                <p>
                                    <?= escapar($datos['descripcion']) ?>
                                </p>

                                <span class="explorar">
                                    Explorar colección →
                                </span>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php else: ?>

            <?php $datosTemporada = $temporadas[$temporadaSeleccionada]; ?>

            <?php if ($errorCategoria): ?>

                <!-- CATEGORÍA NO ENCONTRADA -->

                <section class="mensaje-vacio">

                    <span>✿</span>

                    <h1>Categoría no encontrada</h1>

                    <p>
                        Esa categoría no está registrada en esta temporada.
                        Elige una de las categorías disponibles.
                    </p>

                    <a
                        class="boton-volver"
                        href="catalogo.php?temporada=<?= rawurlencode($temporadaSeleccionada) ?>"
                    >
                        ← Volver a las categorías
                    </a>

                </section>

            <?php else: ?>

                <!-- PORTADA DE LA TEMPORADA -->

                <section class="portada-temporada <?= escapar($datosTemporada['clase']) ?>">

                    <a class="volver" href="catalogo.php">
                        ← Volver a las temporadas
                    </a>

                    <span class="simbolo-portada">
                        <?= escapar($datosTemporada['simbolo']) ?>
                    </span>

                    <p class="section-label">
                        YESS SWEET GARDEN PRESENTA
                    </p>

                    <h1>
                        Colección
                        <span><?= escapar($temporadaSeleccionada) ?></span>
                    </h1>

                    <p><?= escapar($datosTemporada['frase']) ?></p>

                </section>

                <section class="contenido-temporada">

                    <?php if ($categoriaSeleccionada === null): ?>

                        <!-- NIVEL 2: CATEGORÍAS DE LA TEMPORADA -->

                        <div class="introduccion-categorias">

                            <p class="section-label">
                                DESCUBRE TU ESTILO
                            </p>

                            <h2>Explora por tipo de prenda</h2>

                            <p>
                                Selecciona una categoría para ver las prendas
                                de <?= escapar($temporadaSeleccionada) ?>.
                            </p>

                        </div>

                        <?php if (count($categoriasDisponibles) > 0): ?>

                            <div class="rejilla-categorias rejilla-tipos">

                                <?php foreach ($categoriasDisponibles as $categoria): ?>

                                    <a
                                        class="tarjeta-tipo"
                                        data-temporada="<?= escapar($temporadaSeleccionada) ?>"
                                        href="catalogo.php?temporada=<?= rawurlencode($temporadaSeleccionada) ?>&amp;categoria=<?= rawurlencode($categoria) ?>"
                                    >

                                        <span class="tipo-simbolo">✿</span>

                                        <h3><?= escapar($categoria) ?></h3>

                                        <span class="explorar">
                                            Ver prendas →
                                        </span>

                                    </a>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <div class="mensaje-vacio">

                                <span>✿</span>

                                <h2>Esta colección está floreciendo</h2>

                                <p>
                                    Todavía no hay prendas registradas
                                    para esta temporada.
                                </p>

                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <!-- NIVEL 3: PRENDAS DE LA CATEGORÍA -->

                        <a
                            class="boton-volver"
                            href="catalogo.php?temporada=<?= rawurlencode($temporadaSeleccionada) ?>"
                        >
                            ← Volver a las categorías
                        </a>

                        <div class="introduccion-categorias">

                            <p class="section-label">
                                COLECCIÓN <?= escapar($temporadaSeleccionada) ?>
                            </p>

                            <h2><?= escapar($categoriaSeleccionada) ?></h2>

                            <p>
                                <?= count($prendas) ?>
                                <?= count($prendas) === 1
                                    ? 'prenda registrada'
                                    : 'prendas registradas' ?>
                            </p>

                        </div>

                        <?php if (count($prendas) > 0): ?>

                            <div class="rejilla-prendas">

                                <?php foreach ($prendas as $prenda): ?>

                                    <?php
                                    $archivoImagen = trim(
                                        (string) ($prenda['imagen'] ?? '')
                                    );

                                    $imagenValida =
                                        $archivoImagen !== '' &&
                                        basename($archivoImagen) === $archivoImagen &&
                                        preg_match(
                                            '/\.(jpg|jpeg|png|webp)$/i',
                                            $archivoImagen
                                        );
                                    ?>

                                    <article class="tarjeta-prenda">

                                        <div class="foto-prenda">

                                            <?php if ($imagenValida): ?>

                                                <img
                                                    src="imagenes/prendas/<?= rawurlencode($archivoImagen) ?>"
                                                    alt="<?= escapar($prenda['nombre']) ?>"
                                                    loading="lazy"
                                                >

                                            <?php else: ?>

                                                <div class="imagen-pendiente">

                                                    <span>
                                                        <?= escapar($datosTemporada['simbolo']) ?>
                                                    </span>

                                                    <p>Una prenda muy sweet</p>

                                                    <small>
                                                        Fotografía pendiente
                                                    </small>

                                                </div>

                                            <?php endif; ?>

                                            <span class="etiqueta-temporada">
                                                <?= escapar($temporadaSeleccionada) ?>
                                            </span>

                                        </div>

                                        <div class="informacion-prenda">

                                            <span class="categoria">
                                                <?= escapar($prenda['categoria']) ?>
                                            </span>

                                            <h3>
                                                <?= escapar($prenda['nombre']) ?>
                                            </h3>

                                            <p class="descripcion">
                                                <?= escapar(
                                                    $prenda['descripcion']
                                                    ?: 'Una prenda para tu estilo.'
                                                ) ?>
                                            </p>

                                            <div class="detalles-prenda">

                                                <span>
                                                    Talla:
                                                    <?= escapar(
                                                        $prenda['talla']
                                                        ?: 'No especificada'
                                                    ) ?>
                                                </span>

                                                <span>
                                                    Color:
                                                    <?= escapar(
                                                        $prenda['color']
                                                        ?: 'No especificado'
                                                    ) ?>
                                                </span>

                                            </div>

                                            <div class="pie-tarjeta">

                                                <span class="precio">
                                                    $<?= number_format(
                                                        (float) $prenda['precio'],
                                                        2
                                                    ) ?>

                                                    <small>MXN</small>
                                                </span>

                                                <?php if ((int) $prenda['stock'] > 0): ?>

                                                    <span class="disponibilidad">
                                                        Disponible
                                                    </span>

                                                <?php else: ?>

                                                    <span class="agotada">
                                                        Agotada
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </article>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <div class="mensaje-vacio">

                                <span>♡</span>

                                <h2>No hay prendas en esta categoría</h2>

                                <p>
                                    Prueba otra categoría de esta temporada.
                                </p>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        <?php endif; ?>

    </main>

    <!-- PIE DE PÁGINA -->

    <footer class="pie-pagina">

        <a href="inicio.html" class="logo">
            Yess Sweet Garden
        </a>

        <p>Un pequeño jardín para expresar tu estilo. ♡</p>

        <a href="catalogo.php">Volver al catálogo</a>

        <small>© 2026 Yess Sweet Garden</small>

    </footer>

</body>
</html>
