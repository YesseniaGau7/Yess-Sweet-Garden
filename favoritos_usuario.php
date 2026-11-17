<?php
session_name('YSG_CLIENTE');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login_usuario.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

function escaparFavorito($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$usuarioId = (int) $_SESSION['usuario_id'];

$consulta = $conexion->prepare(
    "SELECT p.id, p.nombre, p.descripcion, p.precio,
            p.categoria, p.temporada, p.color, p.imagen_ruta
     FROM favoritos f
     INNER JOIN prendas p ON p.id = f.prenda_id
     WHERE f.usuario_id = ?
     ORDER BY f.fecha_registro DESC"
);

$consulta->execute([$usuarioId]);
$favoritos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis favoritos | Yess Sweet Garden</title>
    <link rel="stylesheet" href="css/estilos-catalogo.css">

    <style>
       

     .pagina-usuario {
    max-width: 1200px;
    margin: 30px auto 50px;
    padding: 0 25px;
}

.volver-perfil {
    margin-bottom: 25px;
}

.volver-perfil a {
    display: inline-block;
    color: #ae2d40;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
}

.volver-perfil a:hover {
    text-decoration: underline;
}

.encabezado-usuario {
    text-align: center;
    margin-bottom: 35px;
}

.encabezado-usuario h1 {
    color: #542d2b;
    font-family: Georgia, serif;
    font-size: 36px;
}

.encabezado-usuario p {
    color: #80645d;
}

.rejilla-favoritos {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 260px));
    justify-content: center;
    gap: 25px;
}

.tarjeta-favorito {
    width: 100%;
    max-width: 260px;
    background: #fff;
    border: 1px solid #eadbd2;
    border-radius: 16px;
    overflow: hidden;
}

.tarjeta-favorito img {
    width: 100%;
    height: 250px;
    object-fit: contain;
    display: block;
    background: #fffaf5;
    padding: 10px;
    box-sizing: border-box;
}

.contenido-favorito {
    padding: 16px;
}

.contenido-favorito h2 {
    color: #542d2b;
    font-family: Georgia, serif;
    font-size: 21px;
    margin: 0 0 10px;
}

.contenido-favorito p {
    color: #80645d;
    font-size: 14px;
    line-height: 1.5;
}

.precio-favorito {
    color: #ae2d40 !important;
    font-size: 19px !important;
    font-weight: bold;
}

.talla-favorito {
    margin: 16px 0;
}

.talla-favorito label {
    display: block;
    color: #705047;
    margin-bottom: 8px;
    font-size: 14px;
}

.selector-talla-favorito {
    width: 100%;
    padding: 11px;
    border: 1px solid #ead8ce;
    border-radius: 10px;
    background: #fffaf5;
    color: #542d2b;
    font-size: 14px;
}

.btn-agregar-carrito-favorito,
.btn-quitar-favorito {
    width: 100%;
    padding: 11px 8px;
    margin-top: 9px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    transition: .2s ease;
}

.btn-agregar-carrito-favorito {
    border: 1px solid #71845b;
    background: #71845b;
    color: white;
}

.btn-agregar-carrito-favorito:hover {
    background: #5e7049;
}

.btn-quitar-favorito {
    border: 1px solid #ae2d40;
    background: white;
    color: #ae2d40;
}

.btn-quitar-favorito:hover {
    background: #ae2d40;
    color: white;
}

.btn-agregar-carrito-favorito:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.sin-tallas-favorito {
    color: #ae2d40 !important;
}

.sin-favoritos {
    text-align: center;
    padding: 45px 20px;
    background: #fff;
    border-radius: 15px;
    color: #80645d;
}

.enlace-catalogo {
    display: inline-block;
    margin-top: 15px;
    color: #ae2d40;
}

@media (max-width: 600px) {
    .rejilla-favoritos {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .tarjeta-favorito {
        max-width: none;
    }

    .tarjeta-favorito img {
        height: 190px;
    }

    .contenido-favorito {
        padding: 12px;
    }
}
        

    
  
    </style>
</head>

<body>

<main class="pagina-usuario">
<div class="volver-perfil">
    <a href="perfil_usuario.php">← Volver a mi perfil</a>
</div>
    <header class="encabezado-usuario">
        <h1>Mis favoritos ♡</h1>
        <p>Las prendas que te han enamorado.</p>
    </header>

    <?php if (count($favoritos) > 0): ?>

        <section class="rejilla-favoritos">

            <?php foreach ($favoritos as $prenda): ?>

               <?php
$archivoImagen = basename((string)($prenda['imagen_ruta'] ?? ''));
$rutaImagen = 'imagenes/prendas/' . rawurlencode($archivoImagen);

// Consultar las tallas disponibles de esta prenda.
$consultaTallas = $conexion->prepare(
    "SELECT talla, stock
     FROM prenda_tallas
     WHERE prenda_id = ? AND stock > 0
     ORDER BY FIELD(talla, 'XS', 'S', 'M', 'L', 'XL', 'Unitalla')"
);

$consultaTallas->execute([$prenda['id']]);
$tallasDisponibles = $consultaTallas->fetchAll(PDO::FETCH_ASSOC);
?>

                <article class="tarjeta-favorito"
                         data-prenda-id="<?= (int)$prenda['id'] ?>">

                    <?php if ($archivoImagen !== ''): ?>
                        <img src="<?= escaparFavorito($rutaImagen) ?>"
                             alt="<?= escaparFavorito($prenda['nombre']) ?>">
                    <?php endif; ?>

                  <div class="contenido-favorito">

    <h2><?= escaparFavorito($prenda['nombre']) ?></h2>

    <p><?= escaparFavorito($prenda['descripcion']) ?></p>

    <p class="precio-favorito">
        $<?= number_format((float)$prenda['precio'], 2) ?> MXN
    </p>

    <div class="talla-favorito">
        <label for="talla-favorito-<?= (int)$prenda['id'] ?>">
            Selecciona tu talla:
        </label>

        <?php if (!empty($tallasDisponibles)): ?>

            <select
                class="selector-talla-favorito"
                id="talla-favorito-<?= (int)$prenda['id'] ?>"
            >
                <option value="">Elige una talla</option>

                <?php foreach ($tallasDisponibles as $datoTalla): ?>
                    <option value="<?= escaparFavorito($datoTalla['talla']) ?>">
                        <?= escaparFavorito($datoTalla['talla']) ?>
                        — <?= (int)$datoTalla['stock'] ?> disponibles
                    </option>
                <?php endforeach; ?>

            </select>

        <?php else: ?>

            <p class="sin-tallas-favorito">Sin tallas disponibles</p>

        <?php endif; ?>
    </div>

    <button
        type="button"
        class="btn-agregar-carrito-favorito"
        data-prenda-id="<?= (int)$prenda['id'] ?>"
        <?= empty($tallasDisponibles) ? 'disabled' : '' ?>
    >
        Agregar al carrito
    </button>

    <button
        type="button"
        class="btn-quitar-favorito"
        data-prenda-id="<?= (int)$prenda['id'] ?>"
    >
        Quitar de favoritos ♡
    </button>

</div>
                </article>

            <?php endforeach; ?>

        </section>

    <?php else: ?>

        <div class="sin-favoritos">
            <h2>Todavía no tienes favoritos ♡</h2>
            <p>Explora nuestro catálogo y guarda las prendas que más te gusten.</p>
            <a class="enlace-catalogo" href="catalogo.php">
                Ir al catálogo
            </a>
        </div>

    <?php endif; ?>

</main>

<script>
document.querySelectorAll('.btn-quitar-favorito').forEach(boton => {
    boton.addEventListener('click', async () => {
        const prendaId = boton.dataset.prendaId;

        try {
            const respuesta = await fetch('favorito_accion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    prenda_id: prendaId
                })
            });

            const resultado = await respuesta.json();

            if (respuesta.status === 401) {
                window.location.href = 'login_usuario.php';
                return;
            }

            if (!resultado.ok) {
                alert(resultado.mensaje);
                return;
            }

            const tarjeta = boton.closest('.tarjeta-favorito');
            tarjeta.remove();

            if (!document.querySelector('.tarjeta-favorito')) {
                location.reload();
            }

        } catch (error) {
            alert('No se pudo actualizar tu lista de favoritos.');
        }
    });
});
document.querySelectorAll('.btn-agregar-carrito-favorito').forEach(boton => {
    boton.addEventListener('click', async () => {

        const tarjeta = boton.closest('.tarjeta-favorito');
        const selector = tarjeta.querySelector('.selector-talla-favorito');

        if (!selector || !selector.value) {
            alert('Primero selecciona una talla.');
            if (selector) selector.focus();
            return;
        }

        try {
            boton.disabled = true;

            const respuesta = await fetch('carrito_agregar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    prenda_id: boton.dataset.prendaId,
                    talla: selector.value
                })
            });

            const resultado = await respuesta.json();

            if (respuesta.status === 401) {
                window.location.href = 'login_usuario.php';
                return;
            }

            if (!resultado.ok) {
                alert(resultado.mensaje);
                return;
            }

            alert(resultado.mensaje);
            window.location.href = resultado.redirigir;

        } catch (error) {
            alert('No fue posible agregar la prenda al carrito.');
        } finally {
            boton.disabled = false;
        }
    });
});
</script>

</body>
</html>