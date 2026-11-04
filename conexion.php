
<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$basedatos = "yess_sweet_garden";

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$basedatos;charset=utf8mb4",
        $usuario,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    die("Error de conexión con la base de datos.");
}
?>
