<?php
session_start();
require '../conexion.php'; // Subimos un nivel para acceder a conexion.php

// ---------------- SEGURIDAD ----------------
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";

// ---------------- ELIMINAR COMENTARIO ----------------
if (isset($_GET['eliminar'])) {
    $idComentario = (int)$_GET['eliminar'];

    // Primero eliminar calificación asociada
    $stmt = $conn->prepare("
        DELETE FROM Calificaciones 
        WHERE IdUsuario = (SELECT IdUsuario FROM Comentarios WHERE IdComentario = ?) 
        AND IdSalon = (SELECT IdSalon FROM Comentarios WHERE IdComentario = ?)
    ");
    $stmt->execute([$idComentario, $idComentario]);

    // Luego eliminar comentario
    $stmt = $conn->prepare("DELETE FROM Comentarios WHERE IdComentario = ?");
    $stmt->execute([$idComentario]);

    $mensaje = "Comentario eliminado correctamente.";
}

// ---------------- EDITAR COMENTARIO ----------------
$editar = null;
if (isset($_GET['editar'])) {
    $idComentario = (int)$_GET['editar'];
    $stmt = $conn->prepare("
        SELECT c.IdComentario, c.Comentario, cal.Puntuacion 
        FROM Comentarios c
        JOIN Calificaciones cal ON cal.IdUsuario = c.IdUsuario AND cal.IdSalon = c.IdSalon
        WHERE c.IdComentario = ?
    ");
    $stmt->execute([$idComentario]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ---------------- ACTUALIZAR COMENTARIO ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar comentario + calificación
    if (isset($_POST['idComentario']) && isset($_POST['comentario']) && isset($_POST['puntuacion'])) {
        $idComentario = (int)$_POST['idComentario'];
        $comentario = trim($_POST['comentario']);
        $puntuacion = (int)$_POST['puntuacion'];

        if ($comentario && $puntuacion >= 1 && $puntuacion <= 5) {
            // Actualizar comentario
            $stmt = $conn->prepare("UPDATE Comentarios SET Comentario = ? WHERE IdComentario = ?");
            $stmt->execute([$comentario, $idComentario]);

            // Actualizar calificación
            $stmt = $conn->prepare("
                UPDATE Calificaciones 
                SET Puntuacion = ? 
                WHERE IdUsuario = (SELECT IdUsuario FROM Comentarios WHERE IdComentario = ?) 
                AND IdSalon = (SELECT IdSalon FROM Comentarios WHERE IdComentario = ?)
            ");
            $stmt->execute([$puntuacion, $idComentario, $idComentario]);

            $mensaje = "Comentario actualizado correctamente.";
            header("Location: gestion_comentarios.php");
            exit;
        } else {
            $mensaje = "⚠️ Comentario o puntuación inválida.";
        }
    }

    // Actualizar estado
    if (isset($_POST['estado']) && isset($_POST['idEstado'])) {
        $idComentario = (int)$_POST['idEstado'];
        $estado = $_POST['estado'] === 'no visible' ? 'no visible' : 'visible';
        $stmt = $conn->prepare("UPDATE Comentarios SET Estado = ? WHERE IdComentario = ?");
        $stmt->execute([$estado, $idComentario]);
        $mensaje = "Estado actualizado correctamente.";
        header("Location: gestion_comentarios.php");
        exit;
    }
}

// ---------------- LISTAR TODOS LOS COMENTARIOS ----------------
$stmt = $conn->prepare("
    SELECT c.IdComentario, u.Nombre AS Usuario, s.Nombre AS Salon, c.Comentario, c.Fecha, c.Estado,
           cal.Puntuacion
    FROM Comentarios c
    INNER JOIN Usuarios u ON c.IdUsuario = u.IdUsuario
    INNER JOIN Salones s ON c.IdSalon = s.IdSalon
    INNER JOIN Calificaciones cal ON cal.IdUsuario = u.IdUsuario AND cal.IdSalon = s.IdSalon
    ORDER BY c.Fecha DESC
");
$stmt->execute();
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Comentarios - Admin</title>
   <style>
    body { 
        font-family: Arial; 
        background: #111111; /* fondo negro */
        margin: 0; 
        padding: 0; 
        color: #FFD700; /* texto dorado */
    }

    header { 
        background: #000000; /* header negro */
        color: #FFD700; /* texto dorado */
        padding: 20px; 
        text-align: center; 
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
    }

    main { 
        padding: 20px; 
    }

    table { 
        width: 100%; 
        border-collapse: collapse; 
        background: #000000; /* tabla negra */
        margin-bottom: 20px; 
        color: #FFD700; /* texto dorado */
        border-radius: 6px; 
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
    }

    th, td { 
        border: 1px solid #FFD700; /* borde dorado */
        padding: 10px; 
        text-align: left; 
        vertical-align: top; 
    }

    th { 
        background: #FFD700; /* cabecera dorada */
        color: #000000; /* texto negro */
    }

    a { 
        color: #FFD700; 
        text-decoration: none; 
        margin-right: 5px; 
        font-weight: bold;
    }

    a:hover { 
        text-decoration: underline; 
        color: #FFC300; /* dorado intenso */
    }

    .mensaje { 
        color: #FFD700; 
        margin-bottom: 15px; 
        font-weight: bold; 
        text-shadow: 1px 1px 2px #000000;
    }

    .form-editar { 
        background: #000000; /* fondo negro */
        padding: 10px; 
        border-radius: 8px; 
        margin-bottom: 10px; 
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
    }

    .form-editar textarea { 
        width: 100%; 
        height: 60px; 
        margin-bottom: 5px; 
        background: #111111; 
        color: #FFD700; 
        border: 1px solid #FFD700;
        border-radius: 5px;
    }

    .form-editar select { 
        margin-bottom: 5px; 
        background: #111111; 
        color: #FFD700; 
        border: 1px solid #FFD700; 
        border-radius: 5px;
    }

    .btn { 
        padding: 5px 10px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-actualizar { 
        background: #FFD700; /* dorado */
        color: #000000; 
    }

    .btn-actualizar:hover { 
        background: #FFC300; /* dorado intenso */
        color: #000000; 
        transform: scale(1.05);
    }

    .btn-cancelar { 
        background: #c0392b; /* rojo oscuro */
        color: #FFD700; 
    }

    .btn-cancelar:hover { 
        background: #e74c3c; /* rojo vivo */
        color: #000000; 
        transform: scale(1.05);
    }
</style>

</head>
<body>

<header>
    <h1>Gestión de Comentarios</h1>
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
            <th>Comentario</th>
            <th>Calificación</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($comentarios as $c): ?>
        <tr>
            <td><?= $c['IdComentario'] ?></td>
            <td><?= htmlspecialchars($c['Usuario']) ?></td>
            <td><?= htmlspecialchars($c['Salon']) ?></td>
            <td>
                <?php if($editar && $editar['IdComentario'] == $c['IdComentario']): ?>
                    <!-- FORMULARIO DE EDICIÓN -->
                    <form method="post" class="form-editar">
                        <input type="hidden" name="idComentario" value="<?= $c['IdComentario'] ?>">
                        <textarea name="comentario" required><?= htmlspecialchars($editar['Comentario']) ?></textarea>
                        <select name="puntuacion" required>
                            <?php for($i=1;$i<=5;$i++): ?>
                                <option value="<?= $i ?>" <?= ($editar['Puntuacion']==$i)?'selected':'' ?>><?= str_repeat("⭐",$i) ?></option>
                            <?php endfor; ?>
                        </select><br>
                        <button type="submit" class="btn btn-actualizar">Actualizar</button>
                        <a href="gestion_comentarios.php" class="btn btn-cancelar">Cancelar</a>
                    </form>
                <?php else: ?>
                    <?= nl2br(htmlspecialchars($c['Comentario'])) ?>
                <?php endif; ?>
            </td>
            <td><?= str_repeat("⭐", $c['Puntuacion']) ?></td>
            <td><?= $c['Fecha'] ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="idEstado" value="<?= $c['IdComentario'] ?>">
                    <select name="estado">
                        <option value="visible" <?= $c['Estado']=='visible'?'selected':'' ?>>Visible</option>
                        <option value="no visible" <?= $c['Estado']=='no visible'?'selected':'' ?>>No Visible</option>
                    </select>
                    <button type="submit" class="btn btn-actualizar">Actualizar</button>
                </form>
            </td>
            <td>
                <?php if(!$editar || $editar['IdComentario'] != $c['IdComentario']): ?>
                    <a href="gestion_comentarios.php?editar=<?= $c['IdComentario'] ?>">✏️ Editar</a>
                    <a href="gestion_comentarios.php?eliminar=<?= $c['IdComentario'] ?>" onclick="return confirm('¿Seguro quieres eliminar este comentario?')">🗑️ Eliminar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>
