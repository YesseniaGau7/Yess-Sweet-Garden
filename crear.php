
<?php
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

    $sql = "INSERT INTO prendas
        (nombre, descripcion, categoria, precio,
         temporada, talla, color, stock)
        VALUES
        (:nombre, :descripcion, :categoria, :precio,
         :temporada, :talla, :color, :stock)";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ":nombre" => $nombre,
        ":descripcion" => $descripcion,
        ":categoria" => $categoria,
        ":precio" => $precio,
        ":temporada" => $temporada,
        ":talla" => $talla,
        ":color" => $color,
        ":stock" => $stock
    ]);

    header("Location: index.html?registro=exitoso");
    exit;

} else {
    header("Location: index.html");
    exit;
}
?>
