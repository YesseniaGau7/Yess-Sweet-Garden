
<?php
require_once "conexion.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("ID de prenda no válido.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $categoria = $_POST["categoria"] ?? "";
    $precio = $_POST["precio"] ?? "";
    $temporada = $_POST["temporada"] ?? "";
    $talla = $_POST["talla"] ?? "";
    $color = trim($_POST["color"] ?? "");
    $stock = $_POST["stock"] ?? "";

    $categorias = [
        "Blusa", "Pantalón", "Vestido", "Falda",
        "Suéter", "Abrigo", "Otro"
    ];

    $temporadas = [
        "Primavera", "Verano", "Otoño", "Invierno"
    ];

    if (
        $nombre === "" ||
        !in_array($categoria, $categorias, true) ||
        !in_array($temporada, $temporadas, true) ||
        !is_numeric($precio) ||
        (float)$precio <= 0 ||
        filter_var($stock, FILTER_VALIDATE_INT) === false ||
        (int)$stock < 0
    ) {
        exit("Error: verifica los datos introducidos.");
    }

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
        ":nombre" => $nombre,
        ":descripcion" => $descripcion,
        ":categoria" => $categoria,
        ":precio" => $precio,
        ":temporada" => $temporada,
        ":talla" => $talla,
        ":color" => $color,
        ":stock" => $stock,
        ":id" => $id
    ]);

    header("Location: listar.php");
    exit;
}

$consulta = $conexion->prepare(
    "SELECT * FROM prendas WHERE id = :id"
);
$consulta->execute([":id" => $id]);
$prenda = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$prenda) {
    exit("No se encontró la prenda solicitada.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar prenda - Yess Sweet Garden</title>
</head>
<body>

    <h1>Yess Sweet Garden</h1>
    <h2>Editar prenda</h2>

    <form method="POST"
          action="editar.php?id=<?= (int)$prenda['id'] ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre"
               value="<?= escapar($prenda['nombre']) ?>" required>
        <br><br>

        <label>Descripción:</label>
        <textarea name="descripcion"><?= escapar($prenda['descripcion']) ?></textarea>
        <br><br>

        <label>Categoría:</label>
        <select name="categoria" required>
            <?php foreach (["Blusa", "Pantalón", "Vestido", "Falda", "Suéter", "Abrigo", "Otro"] as $opcion): ?>
                <option value="<?= escapar($opcion) ?>"
                    <?= $prenda['categoria'] === $opcion ? 'selected' : '' ?>>
                    <?= escapar($opcion) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Precio:</label>
        <input type="number" name="precio" min="0.01" step="0.01"
               value="<?= escapar($prenda['precio']) ?>" required>
        <br><br>

        <label>Temporada:</label>
        <select name="temporada" required>
            <?php foreach (["Primavera", "Verano", "Otoño", "Invierno"] as $opcion): ?>
                <option value="<?= escapar($opcion) ?>"
                    <?= $prenda['temporada'] === $opcion ? 'selected' : '' ?>>
                    <?= escapar($opcion) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label>Talla:</label>
        <input type="text" name="talla"
               value="<?= escapar($prenda['talla'] ?? '') ?>">
        <br><br>

        <label>Color:</label>
        <input type="text" name="color"
               value="<?= escapar($prenda['color'] ?? '') ?>">
        <br><br>

        <label>Existencias:</label>
        <input type="number" name="stock" min="0" step="1"
               value="<?= (int)$prenda['stock'] ?>" required>
        <br><br>

        <button type="submit">Guardar cambios</button>
    </form>

    <p><a href="listar.php">Volver al catálogo</a></p>

</body>
</html>
