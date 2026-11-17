
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/conexion.php";

try {
    // Consultar las prendas registradas
    $sql = "SELECT
                id,
                nombre,
                descripcion,
                categoria,
                precio,
                temporada,
                talla,
                color,
                stock,
                imagen_ruta
            FROM prendas
            ORDER BY id DESC";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $prendas = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Preparar respuesta JSON
    $respuesta = [
        "estado" => "correcto",
        "total" => count($prendas),
        "prendas" => $prendas
    ];

    echo json_encode(
        $respuesta,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "estado" => "error",
        "mensaje" => "No se pudieron consultar las prendas."
    ], JSON_UNESCAPED_UNICODE);
}
?>
