
<?php
require_once __DIR__ . '/sesion.php';
verificarSesion();

require_once __DIR__ . '/conexion.php';

function escapar($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Obtener y validar ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID de prenda no válido.');
}

// Consultar la prenda
$consulta = $conexion->prepare(
    "SELECT * FROM prendas WHERE id = :id"
);
$consulta->execute([':id' => $id]);

$prenda = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$prenda) {
    exit('No se encontró la prenda solicitada.');
}

// Tallas permitidas
$tallasPermitidas = [
    'XS', 'S', 'M', 'L', 'XL', 'Unitalla'
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = $_POST['categoria'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $temporada = $_POST['temporada'] ?? '';
    $color = trim($_POST['color'] ?? '');

    $tallas = $_POST['tallas'] ?? [];
    $existencias = $_POST['stock_tallas'] ?? [];

    $categorias = [
        'Blusa', 'Pantalón', 'Vestido', 'Falda',
        'Suéter', 'Abrigo', 'Otro'
    ];

    $temporadas = [
        'Primavera', 'Verano', 'Otoño', 'Invierno'
    ];

    // Validar información general
    if (
        $nombre === '' ||
        !in_array($categoria, $categorias, true) ||
        !in_array($temporada, $temporadas, true) ||
        !is_numeric($precio) ||
        !is_finite((float)$precio) ||
        (float)$precio <= 0 ||
        !is_array($tallas) ||
        !is_array($existencias)
    ) {
        exit('Error: verifica los datos introducidos.');
    }

    // Validar tallas seleccionadas
    $tallas = array_values(array_unique($tallas));

    if (count($tallas) === 0) {
        exit('Debes seleccionar al menos una talla.');
    }

    foreach ($tallas as $talla) {
        if (!in_array($talla, $tallasPermitidas, true)) {
            exit('Se seleccionó una talla no válida.');
        }
    }

    // Unitalla no puede combinarse con otras tallas
    if (
        in_array('Unitalla', $tallas, true) &&
        count($tallas) > 1
    ) {
        exit('Unitalla no puede combinarse con otras tallas.');
    }

    // Validar existencias
    $totalStock = 0;

    foreach ($tallas as $talla) {

        $cantidad = $existencias[$talla] ?? null;

        if (
            $cantidad === null ||
            filter_var($cantidad, FILTER_VALIDATE_INT) === false ||
            (int)$cantidad < 0
        ) {
            exit('Verifica las existencias de la talla ' . escapar($talla));
        }

        $totalStock += (int)$cantidad;
    }

    // Talla principal temporal para compatibilidad
    $tallaPrincipal = $tallas[0];

    try {

        $conexion->beginTransaction();

        // Actualizar información general
        $sql = "UPDATE prendas SET
            nombre = :nombre,
            descripcion = :descripcion,
            categoria = :categoria,
            precio = :precio,
            temporada = :temporada,
            talla = :talla,
            color = :color,
            stock = :stock
            WHERE id = :id";

        $consulta = $conexion->prepare($sql);

        $consulta->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':categoria' => $categoria,
            ':precio' => $precio,
            ':temporada' => $temporada,
            ':talla' => $tallaPrincipal,
            ':color' => $color,
            ':stock' => $totalStock,
            ':id' => $id
        ]);

        // Preparar inserción o actualización de tallas
        $sqlTalla = "INSERT INTO prenda_tallas
            (prenda_id, talla, stock)
            VALUES
            (:prenda_id, :talla, :stock)
            ON DUPLICATE KEY UPDATE
                stock = VALUES(stock)";

        $consultaTalla = $conexion->prepare($sqlTalla);

        // Guardar tallas seleccionadas
        foreach ($tallas as $talla) {

            $consultaTalla->execute([
                ':prenda_id' => $id,
                ':talla' => $talla,
                ':stock' => (int)$existencias[$talla]
            ]);
        }

        // Las tallas desmarcadas quedan con stock 0
        $marcadores = [];
        $parametros = [':id' => $id];

        foreach ($tallas as $indice => $talla) {
            $marcador = ':talla' . $indice;
            $marcadores[] = $marcador;
            $parametros[$marcador] = $talla;
        }

        $sqlDesactivar = "UPDATE prenda_tallas
            SET stock = 0
            WHERE prenda_id = :id
            AND talla NOT IN (" . implode(',', $marcadores) . ")";

        $consultaDesactivar = $conexion->prepare($sqlDesactivar);
        $consultaDesactivar->execute($parametros);

        // Confirmar cambios
        $conexion->commit();

        header('Location: listar.php?edicion=exitosa');
        exit;

    } catch (Throwable $e) {

        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }

        error_log('Error al editar prenda: ' . $e->getMessage());

        exit('No se pudieron guardar los cambios. Intenta nuevamente.');
    }
}

// Consultar tallas actuales de la prenda
$consultaTallas = $conexion->prepare(
    "SELECT talla, stock
     FROM prenda_tallas
     WHERE prenda_id = :id"
);

$consultaTallas->execute([':id' => $id]);

$tallasActuales = $consultaTallas->fetchAll(PDO::FETCH_KEY_PAIR);

// Si la prenda todavía no tiene tallas en la nueva tabla,
// utilizar temporalmente su talla y stock anteriores.
if (empty($tallasActuales) && !empty($prenda['talla'])) {
    $tallasActuales[$prenda['talla']] = (int)$prenda['stock'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar prenda - Yess Sweet Garden</title>
</head>

<body>

    <h1>Yess Sweet Garden</h1>
    <h2>Editar prenda</h2>

    <form method="POST"
          action="editar.php?id=<?= (int)$prenda['id'] ?>">

        <label>Nombre:</label>
        <input
            type="text"
            name="nombre"
            maxlength="100"
            value="<?= escapar($prenda['nombre']) ?>"
            required
        >
        <br><br>

        <label>Descripción:</label>
        <textarea name="descripcion"><?= escapar($prenda['descripcion']) ?></textarea>
        <br><br>

        <label>Categoría:</label>

        <select name="categoria" required>
            <?php foreach (['Blusa', 'Pantalón', 'Vestido', 'Falda', 'Suéter', 'Abrigo', 'Otro'] as $opcion): ?>
                <option
                    value="<?= escapar($opcion) ?>"
                    <?= $prenda['categoria'] === $opcion ? 'selected' : '' ?>
                >
                    <?= escapar($opcion) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label>Precio:</label>
        <input
            type="number"
            name="precio"
            min="0.01"
            step="0.01"
            value="<?= escapar($prenda['precio']) ?>"
            required
        >
        <br><br>

        <label>Temporada:</label>

        <select name="temporada" required>
            <?php foreach (['Primavera', 'Verano', 'Otoño', 'Invierno'] as $opcion): ?>
                <option
                    value="<?= escapar($opcion) ?>"
                    <?= $prenda['temporada'] === $opcion ? 'selected' : '' ?>
                >
                    <?= escapar($opcion) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label>Color:</label>
        <input
            type="text"
            name="color"
            maxlength="50"
            value="<?= escapar($prenda['color']) ?>"
        >
        <br><br>

        <h3>Tallas y existencias</h3>

        <p>
            Selecciona las tallas que deseas ofrecer
            y modifica sus existencias.
        </p>

        <div class="tallas-grid">

            <?php foreach ($tallasPermitidas as $talla): ?>

                <?php
                    $seleccionada = array_key_exists($talla, $tallasActuales);
                    $cantidad = $tallasActuales[$talla] ?? 0;
                ?>

                <div class="talla-item">

                    <label>
                        <input
                            type="checkbox"
                            name="tallas[]"
                            value="<?= escapar($talla) ?>"
                            <?= $seleccionada ? 'checked' : '' ?>
                        >
                        <?= escapar($talla) ?>
                    </label>

                    <input
                        type="number"
                        name="stock_tallas[<?= escapar($talla) ?>]"
                        min="0"
                        step="1"
                        value="<?= (int)$cantidad ?>"
                        <?= !$seleccionada ? 'disabled' : '' ?>
                    >

                </div>

            <?php endforeach; ?>

        </div>

        <br>

        <button type="submit">Guardar cambios</button>

    </form>

    <p>
        <a href="listar.php">Volver al inventario</a>
    </p>

    <script>
        document.querySelectorAll('.talla-item').forEach(item => {

            const checkbox = item.querySelector(
                'input[type="checkbox"]'
            );

            const cantidad = item.querySelector(
                'input[type="number"]'
            );

            checkbox.addEventListener('change', () => {

                cantidad.disabled = !checkbox.checked;

                if (!checkbox.checked) {
                    cantidad.value = 0;
                }

            });

        });

        // Evitar combinar Unitalla con otras tallas
        const opcionesTalla = document.querySelectorAll(
            'input[name="tallas[]"]'
        );

        opcionesTalla.forEach(opcion => {

            opcion.addEventListener('change', () => {

                const unitalla = document.querySelector(
                    'input[name="tallas[]"][value="Unitalla"]'
                );

                if (opcion.value === 'Unitalla' && opcion.checked) {

                    opcionesTalla.forEach(otra => {

                        if (otra !== unitalla) {
                            otra.checked = false;
                            otra.dispatchEvent(new Event('change'));
                        }

                    });

                } else if (
                    opcion.checked &&
                    opcion.value !== 'Unitalla'
                ) {

                    unitalla.checked = false;
                    unitalla.dispatchEvent(new Event('change'));

                }

            });

        });
    </script>

</body>
</html>
