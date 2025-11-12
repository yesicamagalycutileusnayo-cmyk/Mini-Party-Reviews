<?php
session_start();
require '../conexion.php'; // Subimos un nivel para acceder a conexion.php

// Verificar si hay sesión activa y si es admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Mensaje
$mensaje = "";

// Procesar eliminación de calificación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM Calificaciones WHERE IdCalificacion = ?");
    $stmt->execute([$id]);
    $mensaje = "Calificación eliminada correctamente.";
}

// Listar calificaciones
$stmt = $conn->prepare("
    SELECT c.IdCalificacion, u.Nombre AS Usuario, s.Nombre AS Salon, c.Puntuacion, c.Fecha
    FROM Calificaciones c
    INNER JOIN Usuarios u ON c.IdUsuario = u.IdUsuario
    INNER JOIN Salones s ON c.IdSalon = s.IdSalon
    ORDER BY c.Fecha DESC
");
$stmt->execute();
$calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Calificaciones - Admin</title>
    <style>
        body { font-family: Arial; background: #e0f7f5; margin: 0; padding: 0; }
        header { background: #1abc9c; color: white; padding: 20px; text-align: center; }
        main { padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #16a085; color: white; }
        a { color: #1abc9c; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .mensaje { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>

<header>
    <h1>Gestión de Calificaciones</h1>
    <a href="admin_index.php" style="color:white;">⬅ Volver al Panel</a>
</header>

<main>

<?php if($mensaje): ?>
    <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Salón</th>
            <th>Puntuación</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($calificaciones as $calificacion): ?>
        <tr>
            <td><?= $calificacion['IdCalificacion'] ?></td>
            <td><?= htmlspecialchars($calificacion['Usuario']) ?></td>
            <td><?= htmlspecialchars($calificacion['Salon']) ?></td>
            <td><?= $calificacion['Puntuacion'] ?></td>
            <td><?= $calificacion['Fecha'] ?></td>
            <td>
                <a href="gestion_calificaciones.php?eliminar=<?= $calificacion['IdCalificacion'] ?>" onclick="return confirm('¿Seguro quieres eliminar esta calificación?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>
