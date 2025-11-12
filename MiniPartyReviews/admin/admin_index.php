<?php
session_start();
require '../conexion.php';

// Verificar si hay sesión activa y si es admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener nombre de usuario para mostrar en el panel
$nombreAdmin = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Mini Party Reviews</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111111;
            margin: 0;
            color: #FFD700;
        }

        header {
            background: #000000;
            color: #FFD700;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        header h1 { 
            margin: 0; 
            font-size: 28px;
            letter-spacing: 1px;
            text-shadow: 1px 1px 3px rgba(255, 215, 0, 0.7);
        }

        .logout-btn {
            background: #FFD700;
            color: #000000;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 5px 10px rgba(255, 215, 0, 0.3);
            transition: all 0.3s ease;
        }

        .logout-btn:hover { 
            background: #FFC300;
            color: #000;
            transform: scale(1.05);
        }

        nav {
            background-color: #000000;
            padding: 15px 40px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            margin: 20px 40px 0 40px;
        }

        nav a {
            color: #FFD700;
            text-decoration: none;
            margin-right: 25px;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav a:hover { 
            color: #FFC300;
            text-decoration: underline; 
        }

        nav span.current {
            color: #FFC300;
            font-weight: bold;
            margin-right: 25px;
        }

        main {
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            min-height: 70vh;
        }

        .video-banner {
            flex: 1 1 700px;
            min-width: 300px;
            max-width: 900px;
            height: 450px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
            position: relative;
        }

        .video-banner iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-banner-text {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: #FFD700;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.7);
        }

        .image-box {
            flex: 0 0 300px;
            min-width: 250px;
            max-width: 300px;
            height: 450px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }

        .image-box img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 12px;
        }

        @media(max-width:768px){
            main {
                flex-direction: column;
                align-items: center;
            }

            .video-banner, .image-box {
                max-width: 90%;
                height: 300px;
            }

            .video-banner-text {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Panel de Administración</h1>
    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
</header>

<nav>
    <?php if(basename($_SERVER['PHP_SELF']) !== 'admin_index.php'): ?>
        <a href="admin_index.php">Inicio</a>
    <?php else: ?>
        <span class="current">Inicio</span>
    <?php endif; ?>
    <a href="gestion_salones.php">Salones</a>
    <a href="gestion_comentarios.php">Comentarios</a>
    <a href="gestion_usuarios.php">Usuarios</a>
    <a href="gestion_zonas.php">Zonas</a>
    <a href="gestion_fotos.php">Fotos</a>
</nav>

<main>
    <!-- Video grande -->
    <div class="video-banner">
        <iframe src="https://www.youtube.com/embed/XFu3YVGoEB8?autoplay=1&loop=1&playlist=XFu3YVGoEB8" 
                allow="autoplay; encrypted-media" allowfullscreen></iframe>
        <div class="video-banner-text">
            Bienvenido, <?= htmlspecialchars($nombreAdmin) ?> 🎉
        </div>
    </div>

    <!-- Imagen lateral -->
    <div class="image-box">
        <img src="https://i.pinimg.com/736x/f9/db/01/f9db01efbae3cc0e219156ddda3f7a8b.jpg" alt="Imagen Administrador">
    </div>
</main>

</body>
</html>
