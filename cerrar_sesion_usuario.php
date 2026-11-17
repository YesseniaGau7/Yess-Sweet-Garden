
<?php
session_name('YSG_CLIENTE');
session_start();

// Eliminar los datos de la sesión del cliente
$_SESSION = [];

// Eliminar la cookie de sesión
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

session_destroy();

header('Location: inicio.html');
exit;
