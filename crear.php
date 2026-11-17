
<?php
require_once __DIR__ . '/sesion.php';
verificarSesion();

require_once __DIR__ . '/conexion.php';

// Solo aceptar solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Recibir datos generales
$nombre = trim($_POST["nombre"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$categoria = $_POST["categoria"] ?? "";
$precio = $_POST["precio"] ?? "";
$temporada = $_POST["temporada"] ?? "";
$color = trim($_POST["color"] ?? "");

// Recibir tallas y existencias
$tallas = $_POST["tallas"] ?? [];
$existencias = $_POST["stock_tallas"] ?? [];

$categoriasPermitidas = [
    "Blusa", "Pantalón", "Vestido", "Falda",
    "Suéter", "Abrigo", "Otro"
];

$temporadasPermitidas = [
    "Primavera", "Verano", "Otoño", "Invierno"
];

$tallasPermitidas = [
    "XS", "S", "M", "L", "XL", "Unitalla"
];

// Validar datos generales
if (
    $nombre === "" ||
    !in_array($categoria, $categoriasPermitidas, true) ||
    !in_array($temporada, $temporadasPermitidas, true) ||
    !is_numeric($precio) ||
    !is_finite((float)$precio) ||
    (float)$precio <= 0 ||
    !is_array($tallas) ||
    !is_array($existencias) ||
    count($tallas) === 0
) {
    exit("Error: verifica los datos de la prenda.");
}

// Validar tallas seleccionadas
$tallas = array_values(array_unique($tallas));

foreach ($tallas as $talla) {
    if (!in_array($talla, $tallasPermitidas, true)) {
        exit("Error: se seleccionó una talla no válida.");
    }
}

// Unitalla no puede combinarse con otras tallas
if (
    in_array("Unitalla", $tallas, true) &&
    count($tallas) > 1
) {
    exit("Unitalla no puede combinarse con otras tallas.");
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
        exit("Error: verifica las existencias de la talla " . htmlspecialchars($talla));
    }

    $totalStock += (int)$cantidad;
}

// Talla principal temporal para compatibilidad con el catálogo antiguo
$tallaPrincipal = $tallas[0];

// Preparar carpeta de imágenes
$carpetaImagenes = __DIR__ . "/imagenes/prendas/";

if (!is_dir($carpetaImagenes)) {
    if (!mkdir($carpetaImagenes, 0755, true)) {
        exit("No se pudo crear la carpeta de imágenes.");
    }
}

$nombreImagen = null;
$rutaDestino = null;

// Procesar fotografía si se seleccionó
if (
    isset($_FILES["imagen"]) &&
    $_FILES["imagen"]["error"] !== UPLOAD_ERR_NO_FILE
) {
    if ($_FILES["imagen"]["error"] !== UPLOAD_ERR_OK) {
        exit("Error al cargar la fotografía.");
    }

    // Límite de 5 MB
    if ($_FILES["imagen"]["size"] > 5 * 1024 * 1024) {
        exit("La fotografía no debe superar los 5 MB.");
    }

    // Comprobar tipo real del archivo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipoImagen = $finfo->file($_FILES["imagen"]["tmp_name"]);

    $tiposPermitidos = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($tiposPermitidos[$tipoImagen])) {
        exit("Formato no permitido. Utiliza JPG, PNG o WEBP.");
    }

    // Nombre aleatorio
    $nombreImagen = bin2hex(random_bytes(16))
        . "." . $tiposPermitidos[$tipoImagen];

    $rutaDestino = $carpetaImagenes . $nombreImagen;

    if (!move_uploaded_file(
        $_FILES["imagen"]["tmp_name"],
        $rutaDestino
    )) {
        exit("No se pudo guardar la fotografía.");
    }
}

// Registrar prenda y tallas
try {
    $conexion->beginTransaction();

    // Insertar información general de la prenda
    $sql = "INSERT INTO prendas
        (nombre, descripcion, categoria, precio,
         temporada, talla, color, stock, imagen_ruta)
        VALUES
        (:nombre, :descripcion, :categoria, :precio,
         :temporada, :talla, :color, :stock, :imagen_ruta)";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ":nombre" => $nombre,
        ":descripcion" => $descripcion,
        ":categoria" => $categoria,
        ":precio" => $precio,
        ":temporada" => $temporada,
        ":talla" => $tallaPrincipal,
        ":color" => $color,
        ":stock" => $totalStock,
        ":imagen_ruta" => $nombreImagen
    ]);

    // Obtener ID de la prenda recién creada
    $prendaId = $conexion->lastInsertId();

    // Insertar cada talla con sus existencias
    $sqlTalla = "INSERT INTO prenda_tallas
        (prenda_id, talla, stock)
        VALUES
        (:prenda_id, :talla, :stock)";

    $consultaTalla = $conexion->prepare($sqlTalla);

    foreach ($tallas as $talla) {
        $consultaTalla->execute([
            ":prenda_id" => $prendaId,
            ":talla" => $talla,
            ":stock" => (int)$existencias[$talla]
        ]);
    }

    // Confirmar ambas operaciones
    $conexion->commit();

    header("Location: index.php?registro=exitoso");
    exit;

} catch (Throwable $e) {

    // Revertir cambios en la base de datos
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    // Eliminar fotografía si ocurrió un error
    if (
        $rutaDestino !== null &&
        is_file($rutaDestino)
    ) {
        unlink($rutaDestino);
    }

    error_log("Error al registrar prenda: " . $e->getMessage());

    exit("No se pudo registrar la prenda. Revisa los datos e intenta nuevamente.");
}
?>
