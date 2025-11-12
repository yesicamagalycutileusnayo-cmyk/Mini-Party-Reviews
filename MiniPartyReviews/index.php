<?php
session_start();
require 'conexion.php'; // conexión PDO

// Consulta: obtener los 3 salones con mejor promedio
$sql = "
    SELECT s.IdSalon, s.Nombre, s.Descripcion, z.Nombre AS Zona, 
           IFNULL(ROUND(AVG(c.Puntuacion),1),0) AS Promedio
    FROM Salones s
    INNER JOIN Zonas z ON s.IdZona = z.IdZona
    LEFT JOIN Calificaciones c ON c.IdSalon = s.IdSalon
    GROUP BY s.IdSalon, s.Nombre, s.Descripcion, z.Nombre
    ORDER BY Promedio DESC
    LIMIT 3
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las fotos de los salones destacados
$idsDestacados = array_column($destacados, 'IdSalon');
$fotosPorSalon = [];
if ($idsDestacados) {
    $placeholders = implode(',', array_fill(0, count($idsDestacados), '?'));
    $stmtFotos = $conn->prepare("SELECT IdSalon, UrlFoto FROM Fotos WHERE IdSalon IN ($placeholders) ORDER BY IdFoto ASC");
    $stmtFotos->execute($idsDestacados);
    $fotosData = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fotosData as $foto) {
        $fotosPorSalon[$foto['IdSalon']][] = $foto['UrlFoto'];
    }
}

// Función para dibujar estrellas
function mostrarEstrellas($promedio) {
    $estrellas = "";
    $puntos = round($promedio);
    for ($i = 1; $i <= 5; $i++) {
        $estrellas .= ($i <= $puntos) ? "⭐" : "☆";
    }
    return $estrellas;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mini Party Reviews</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
body { margin: 0; font-family: 'Roboto', sans-serif; background-color: #e0f7f5; }
header { background-color: #1abc9c; color: white; padding: 30px 40px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.2); position: relative; }
.logo { height: 140px; margin-bottom: 15px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); transition: transform 0.3s; }
.logo:hover { transform: scale(1.05); }
.login-btn { position: absolute; top: 20px; right: 40px; background: white; color: #1abc9c; padding: 10px 15px; border-radius: 10px; text-decoration: none; font-weight: bold; }
.banner { background: url('img/banner.jpg') center/cover no-repeat; height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }
.section { padding: 60px 40px; text-align: center; }
.card-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 25px; }
.card { background: white; border-radius: 15px; width: 260px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.4s, box-shadow 0.4s; cursor: pointer; }
.card:hover { transform: translateY(-10px) scale(1.05); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
.carousel { position: relative; width: 100%; height: 160px; overflow: hidden; }
.carousel img { width: 100%; height: 160px; object-fit: cover; position: absolute; top: 0; left: 0; transition: opacity 1s ease-in-out; opacity: 0; }
.carousel img.active { opacity: 1; }
.card-body { padding: 20px; }
.card-body h3 { margin: 0 0 10px; color: #1abc9c; }
.card-body p { margin: 0 0 10px; color: #666; }
.card-body .stars { font-size: 18px; color: #ffd700; }
footer { background: #16a085; color: white; text-align: center; padding: 25px; }
.steps { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; margin-top: 30px; }
.step { background: white; padding: 20px; border-radius: 15px; width: 220px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
.step h3 { margin-bottom: 10px; color:#1abc9c; }
</style>
</head>
<body>

<header>
    <div class="header-content">
        <img src="img/logo.jpeg" alt="Mini Party Reviews" class="logo">
        <h1>Mini Party Reviews 🎉</h1>
    </div>
    <?php if(!isset($_SESSION['rol'])): ?>
        <a href="login.php" class="login-btn">Iniciar Sesión (Admin)</a>
    <?php elseif($_SESSION['rol'] === 'admin'): ?>
        <a href="logout.php" class="login-btn">Cerrar Sesión</a>
    <?php endif; ?>
</header>

<div class="banner">
    <h1>¡Encuentra el salón perfecto para tu fiesta!</h1>
    <p>Compara, comenta y califica tus salones favoritos</p>
    <a href="Salones/mapa.php" class="cta-btn" style="padding:10px 20px;background:#1abc9c;color:white;border-radius:10px;text-decoration:none;">Ver Salones</a>
</div>

<section class="section">
    <h2>Salones Destacados ⭐</h2>
    <div class="card-container">
        <?php if ($destacados): ?>
            <?php foreach ($destacados as $salon): 
                $fotos = $fotosPorSalon[$salon['IdSalon']] ?? ['img/default.jpg'];
            ?>
                <div class="card">
                    <div class="carousel">
                        <?php foreach($fotos as $i => $foto): ?>
                            <img src="<?= htmlspecialchars($foto) ?>" class="<?= $i===0 ? 'active' : '' ?>" alt="<?= htmlspecialchars($salon['Nombre']) ?>">
                        <?php endforeach; ?>
                    </div>
                    <div class="card-body">
                        <h3><?= htmlspecialchars($salon['Nombre']) ?></h3>
                        <p><?= htmlspecialchars($salon['Zona']) ?></p>
                        <div class="stars"><?= mostrarEstrellas($salon['Promedio']) ?> (<?= $salon['Promedio'] ?>)</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aún no hay salones destacados 😢</p>
        <?php endif; ?>
    </div>
</section>

<section class="section" style="background:#d0f7f5;">
    <h2>Cómo Funciona</h2>
    <div class="steps">
        <div class="step"><h3>1. Explora</h3><p>Descubre los salones de tu ciudad y mira sus fotos.</p></div>
        <div class="step"><h3>2. Comenta</h3><p>Deja tu opinión y calificación usando un nombre único.</p></div>
        <div class="step"><h3>3. Disfruta</h3><p>Selecciona el salón perfecto y organiza tu fiesta.</p></div>
    </div>
</section>

<footer>
    <p>🎈 Mini Party Reviews - Diversión y alegría para todos</p>
    <p>✨ Inspira sonrisas con tus fiestas infantiles</p>
    <p>💡 Comparte tu experiencia y haz memorable cada celebración</p>
</footer>

<script>
const carousels = document.querySelectorAll('.carousel');
carousels.forEach(carousel => {
    const images = carousel.querySelectorAll('img');
    let index = 0;
    setInterval(() => {
        images[index].classList.remove('active');
        index = (index + 1) % images.length;
        images[index].classList.add('active');
    }, 3000);
});
</script>

</body>
</html>
