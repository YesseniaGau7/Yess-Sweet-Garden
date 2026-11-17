<?php

$servidor = "sql108.infinityfree.com";
$usuario = "if0_43451823";
$password = "YessSweet123";
$basedatos = "if0_43451823_ysg_tienda";

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