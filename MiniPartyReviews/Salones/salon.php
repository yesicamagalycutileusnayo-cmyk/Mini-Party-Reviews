<?php
session_start();
require '../conexion.php';

if(!isset($_GET['id'])) {
    header("Location: mapa.php");
    exit;
}

$idSalon = (int)$_GET['id'];

// ---------------- DATOS DEL SALÓN ----------------
$stmt = $conn->prepare("SELECT * FROM Salones WHERE IdSalon = ?");
$stmt->execute([$idSalon]);
$salon = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$salon) {
    echo "Salón no encontrado";
    exit;
}

// ---------------- FOTOS DESDE BASE DE DATOS ----------------
$stmtFotos = $conn->prepare("SELECT UrlFoto FROM Fotos WHERE IdSalon = ?");
$stmtFotos->execute([$idSalon]);
$fotos = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);

// Ajustar ruta para mostrar correctamente desde salon.php
if ($fotos) {
    foreach($fotos as &$foto) {
        if (strpos($foto, "../") !== 0) {
            $foto = "../" . $foto;
        }
    }
    unset($foto);
} else {
    $fotos = ['../img/no-image.png']; 
}

// ---------------- VALIDACIONES DE COMENTARIO ----------------
$palabrasProhibidas = [
    'imbecil','pendejo','cabron','culero','gilipollas','burro','retard','bastardo','malparido','mongol',
    'cabrón','zorra','perra','hijo de puta','imbécil','idiota de mierda','trol','cretino','tarado',
    'hdp','mamón','baboso','cagón','maricón','marica','pajero','pelotudo','choto','boludo',
    'idiota sucio','idiota completo','pinche','bobo','burra','cabron','cerdo','mierdita','tonto del culo',

    'select','drop','delete','insert','update','truncate','alter','create','table','database',
    'union','--',';','or','and','exec','declare','char','nchar','varchar','nvarchar',
    'cast','convert','script','alert','onerror','onload','sleep','benchmark','load_file','outfile'
];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $comentario = trim($_POST['comentario']);
    $puntuacion = intval($_POST['puntuacion']);

    if (empty($nombre) || empty($comentario) || empty($puntuacion)) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {
        $texto = strtolower($nombre . " " . $comentario);
        $contieneProhibida = false;
        foreach ($palabrasProhibidas as $palabra) {
            if (strpos($texto, strtolower($palabra)) !== false) {
                $contieneProhibida = true;
                break;
            }
        }

        if ($contieneProhibida) {
            $error = "⚠️ No se permiten groserías.";
        } else {
            $stmt = $conn->prepare("
                SELECT u.IdUsuario
                FROM Usuarios u
                JOIN Comentarios c ON c.IdUsuario = u.IdUsuario
                WHERE u.Nombre = ? AND c.IdSalon = ?
            ");
            $stmt->execute([$nombre, $idSalon]);
            $existe = $stmt->fetchColumn();

            if ($existe) {
                $error = "⚠️ Ese nombre ya comentó en este salón.";
            } else {
                // Crear usuario
                $stmt = $conn->prepare("INSERT INTO Usuarios (Nombre, Rol) VALUES (?, 'usuario')");
                $stmt->execute([$nombre]);
                $idUsuario = $conn->lastInsertId();

                // Insertar comentario
                $stmt = $conn->prepare("INSERT INTO Comentarios (IdUsuario, IdSalon, Comentario) VALUES (?, ?, ?)");
                $stmt->execute([$idUsuario, $idSalon, $comentario]);

                // Insertar calificación
                $stmt = $conn->prepare("INSERT INTO Calificaciones (IdUsuario, IdSalon, Puntuacion) VALUES (?, ?, ?)");
                $stmt->execute([$idUsuario, $idSalon, $puntuacion]);

                header("Location: salon.php?id=$idSalon&ok=1");
                exit;
            }
        }
    }
}

// ---------------- COMENTARIOS PUBLICADOS ----------------
$stmt = $conn->prepare("
    SELECT u.Nombre, c.Comentario, DATE(c.Fecha) AS Fecha, cal.Puntuacion
    FROM Comentarios c
    JOIN Usuarios u ON u.IdUsuario = c.IdUsuario
    JOIN Calificaciones cal ON cal.IdUsuario = u.IdUsuario AND cal.IdSalon = c.IdSalon
    WHERE c.IdSalon = ? AND c.Estado = 'visible'
    ORDER BY c.Fecha DESC
");
$stmt->execute([$idSalon]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($salon['Nombre']) ?></title>
    <style>
        body { font-family: Arial,sans-serif; background:#e0f7f5; margin:0; padding:20px; text-align:center; }
        h1 { color:#1abc9c; }

        .carousel { position: relative; width: 600px; height: 350px; margin: 0 auto; overflow: hidden; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.2); }
        .carousel img { width: 100%; height: 350px; object-fit: cover; position: absolute; top:0; left:0; transition: opacity 1s ease-in-out; opacity:0; }
        .carousel img.active { opacity:1; }

        .info { margin-top:20px; max-width:800px; margin-left:auto; margin-right:auto; text-align:left; }
        .info p { color:#555; font-size:18px; margin:5px 0; }
        .map-link { display:inline-block; margin-top:15px; padding:10px 15px; background:#1abc9c; color:white; border-radius:10px; text-decoration:none; }
        .map-link:hover { background:#16a085; }

        /* Formulario */
        .formulario { background:white; padding:20px; border-radius:15px; max-width:600px; margin:30px auto; box-shadow:0 4px 10px rgba(0,0,0,0.2); text-align:left; }
        .formulario label { display:block; margin-top:10px; font-weight:bold; }
        .formulario input, .formulario textarea, .formulario select { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:8px; }
        .formulario button { margin-top:15px; background:#1abc9c; color:white; border:none; padding:12px 20px; border-radius:10px; cursor:pointer; font-size:16px; }
        .formulario button:hover { background:#16a085; }

        /* Comentarios */
        .comentarios { max-width:700px; margin:40px auto; text-align:left; }
        .comentario { background:white; padding:15px; border-radius:10px; margin-bottom:15px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        .comentario h4 { margin:0; color:#1abc9c; }
        .comentario p { margin:5px 0; }
        .stars { color:#ffd700; font-size:18px; }
        .error { color:red; font-weight:bold; margin-bottom:10px; }
        .ok { color:green; font-weight:bold; margin-bottom:10px; }

        /* Botón regresar */
        .btn-regresar { display:inline-block; margin-top:30px; padding:10px 15px; background:#1abc9c; color:white; border-radius:10px; text-decoration:none; font-weight:bold; }
        .btn-regresar:hover { background:#16a085; }
    </style>
</head>
<body>

<h1><?= htmlspecialchars($salon['Nombre']) ?></h1>

<div class="carousel">
    <?php foreach($fotos as $index => $foto): ?>
        <img src="<?= htmlspecialchars($foto) ?>" class="<?= $index===0 ? 'active' : '' ?>" alt="Foto del salón">
    <?php endforeach; ?>
</div>

<div class="info">
    <p><strong>Descripción:</strong> <?= htmlspecialchars($salon['Descripcion']) ?></p>
    <p><strong>Dirección:</strong> <?= htmlspecialchars($salon['Direccion']) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($salon['Telefono']) ?></p>
    <a href="<?= htmlspecialchars($salon['LinkMaps']) ?>" target="_blank" class="map-link">Ver en Google Maps</a>
</div>

<div class="formulario">
    <h2>Deja tu comentario</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['ok'])): ?>
        <div class="ok">✅ Comentario publicado con éxito.</div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Tu Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Comentario:</label>
        <textarea name="comentario" rows="4" required></textarea>

        <label>Calificación:</label>
        <select name="puntuacion" required>
            <option value="">--Selecciona--</option>
            <option value="1">⭐</option>
            <option value="2">⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
        </select>

        <button type="submit">Publicar</button>
    </form>
</div>

<div class="comentarios">
    <h2>Comentarios</h2>
    <?php if ($comentarios): ?>
        <?php foreach ($comentarios as $c): ?>
            <div class="comentario">
                <h4><?= htmlspecialchars($c['Nombre']) ?></h4>
                <div class="stars"><?= str_repeat("⭐", $c['Puntuacion']) ?></div>
                <p><?= nl2br(htmlspecialchars($c['Comentario'])) ?></p>
                <small>📅 <?= $c['Fecha'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay comentarios aún. ¡Sé el primero en opinar! 🎉</p>
    <?php endif; ?>
</div>

<!-- Botón regresar debajo de los comentarios -->
<a href="mapa.php" class="btn-regresar">⬅️ Regresar</a>

<script>
    // Carrusel automático
    const carousel = document.querySelector('.carousel');
    const images = carousel.querySelectorAll('img');
    let index = 0;
    setInterval(() => {
        images[index].classList.remove('active');
        index = (index + 1) % images.length;
        images[index].classList.add('active');
    }, 3000);
</script>

</body>
</html>
