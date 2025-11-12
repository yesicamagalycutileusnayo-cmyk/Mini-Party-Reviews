<?php
session_start();
require '../conexion.php';

// Obtener todos los salones
$stmt = $conn->prepare("SELECT IdSalon, Nombre, Descripcion FROM Salones");
$stmt->execute();
$salones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las fotos de la base de datos
$stmtFotos = $conn->prepare("SELECT IdSalon, UrlFoto FROM Fotos");
$stmtFotos->execute();
$fotoData = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

// Organizar fotos por salón
$fotosPorSalon = [];
foreach ($fotoData as $foto) {
    $fotosPorSalon[$foto['IdSalon']][] = '../' . $foto['UrlFoto']; // Ajusta la ruta según tu estructura
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Salones</title>
<style>
body { font-family: Arial,sans-serif; background:#e0f7f5; margin:0; padding:20px; text-align:center; }
h1 { color:#1abc9c; margin-bottom: 10px; }
header { position: relative; padding: 10px 0; }
.back-btn {
    position: absolute;
    left: 20px;
    top: 20px;
    background: #1abc9c;
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
.back-btn:hover {
    background: #16a085;
    transform: scale(1.05);
}
.salones-container { display:flex; flex-wrap:wrap; justify-content:center; gap:25px; margin-top:30px; }
.salon-card { background:white; width:300px; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.2); overflow:hidden; transition: transform 0.3s; cursor:pointer; }
.salon-card:hover { transform:translateY(-5px) scale(1.03); box-shadow:0 10px 25px rgba(0,0,0,0.3); }
.carousel { position: relative; width: 100%; height: 180px; overflow: hidden; border-bottom: 1px solid #ddd; }
.carousel img { width: 100%; height: 180px; object-fit: cover; position: absolute; top: 0; left: 0; transition: opacity 1s ease-in-out; opacity: 0; }
.carousel img.active { opacity: 1; }
.salon-body { padding:15px; text-align:left; }
.salon-body h3 { margin:0 0 10px; color:#1abc9c; }
.salon-body p { margin:0; color:#666; }
.ver-btn { margin-top:10px; display:inline-block; padding:10px 15px; background:#1abc9c; color:white; border-radius:10px; text-decoration:none; font-weight:bold; transition: all 0.3s; }
.ver-btn:hover { background:#16a085; transform:scale(1.05); }
</style>
</head>
<body>

<header>
    <a href="../index.php" class="back-btn">⬅ Volver al Inicio</a>
    <h1>Salones Disponibles</h1>
</header>

<div class="salones-container">
    <?php foreach($salones as $salon): 
        $fotos = $fotosPorSalon[$salon['IdSalon']] ?? ['../img/no-image.png']; // si no hay fotos
    ?>
        <div class="salon-card">
            <div class="carousel">
                <?php foreach($fotos as $index => $foto): ?>
                    <img src="<?php echo htmlspecialchars($foto); ?>" class="<?php echo $index===0 ? 'active' : ''; ?>" alt="<?php echo htmlspecialchars($salon['Nombre']); ?>">
                <?php endforeach; ?>
            </div>
            <div class="salon-body">
                <h3><?php echo htmlspecialchars($salon['Nombre']); ?></h3>
                <p><?php echo htmlspecialchars($salon['Descripcion']); ?></p>
                <a href="salon.php?id=<?php echo $salon['IdSalon']; ?>" class="ver-btn">Ver Salón</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Carrusel automático
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        const images = carousel.querySelectorAll('img');
        let index = 0;
        setInterval(() => {
            images[index].classList.remove('active');
            index = (index + 1) % images.length;
            images[index].classList.add('active');
        }, 3000); // Cambia cada 3 segundos
    });
</script>

</body>
</html>
