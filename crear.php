
<?php
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

// Recibir datos
$nombre = trim($_POST["nombre"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$categoria = $_POST["categoria"] ?? "";
$precio = $_POST["precio"] ?? "";
$temporada = $_POST["temporada"] ?? "";
$talla = $_POST["talla"] ?? "";
$color = trim($_POST["color"] ?? "");
$stock = $_POST["stock"] ?? "";

$categoriasPermitidas = [
    "Blusa", "Pantalón", "Vestido", "Falda",
    "Suéter", "Abrigo", "Otro"
];

$temporadasPermitidas = [
    "Primavera", "Verano", "Otoño", "Invierno"
];

// Validar datos
if (
    $nombre === "" ||
    !in_array($categoria, $categoriasPermitidas, true) ||
    !in_array($temporada, $temporadasPermitidas, true) ||
    !is_numeric($precio) ||
    (float)$precio <= 0 ||
    filter_var($stock, FILTER_VALIDATE_INT) === false ||
    (int)$stock < 0
) {
    exit("Error: verifica los datos de la prenda.");
}

// Preparar carpeta de imágenes
$carpetaImagenes = __DIR__ . "/imagenes/prendas/";

if (!is_dir($carpetaImagenes)) {
    if (!mkdir($carpetaImagenes, 0755, true)) {
        exit("No se pudo crear la carpeta de imágenes.");
    }
}

$nombreImagen = null;

// Procesar fotografía si se seleccionó una
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

    // Comprobar el tipo real del archivo
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

    // Crear nombre aleatorio
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

// Insertar prenda
try {
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
        ":talla" => $talla,
        ":color" => $color,
        ":stock" => $stock,
        ":imagen_ruta" => $nombreImagen
    ]);

    header("Location: index.html?registro=exitoso");
    exit;

} catch (PDOException $e) {

    // Si falla el registro, eliminar la imagen recién subida
    if ($nombreImagen !== null) {
        $rutaDestino = $carpetaImagenes . $nombreImagen;

        if (is_file($rutaDestino)) {
            unlink($rutaDestino);
        }
    }

    error_log("Error al registrar prenda: " . $e->getMessage());
    exit("No se pudo registrar la prenda. Intenta nuevamente.");
}
?>
