
<?php
session_name('YSG_CLIENTE');
session_start();

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'sesion' => true,
        'nombre' => $_SESSION['usuario_nombre'] ?? ''
    ]);
} else {
    echo json_encode([
        'sesion' => false
    ]);
}
