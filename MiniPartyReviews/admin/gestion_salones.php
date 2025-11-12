<?php
session_start();
require '../conexion.php'; // Subimos un nivel para acceder a conexion.php

// Verificar sesión y rol admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";

// Agregar salón
if (isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $idzona = $_POST['idzona'];
    $descripcion = trim($_POST['descripcion']);

    if ($nombre && $direccion && $idzona) {
        $stmt = $conn->prepare("INSERT INTO Salones (IdZona, Nombre, Direccion, Telefono, Descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$idzona, $nombre, $direccion, $telefono, $descripcion]);
        $mensaje = "✅ Salón agregado correctamente.";
    } else {
        $mensaje = "⚠️ Nombre, Dirección y Zona son obligatorios.";
    }
}

// Eliminar salón
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM Salones WHERE IdSalon = ?");
    $stmt->execute([$id]);
    $mensaje = "🗑️ Salón eliminado correctamente.";
}

// Obtener salón para editar
$salonEditar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM Salones WHERE IdSalon = ?");
    $stmt->execute([$id]);
    $salonEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Guardar edición
if (isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $idzona = $_POST['idzona'];
    $descripcion = trim($_POST['descripcion']);

    $stmt = $conn->prepare("UPDATE Salones SET IdZona=?, Nombre=?, Direccion=?, Telefono=?, Descripcion=? WHERE IdSalon=?");
    $stmt->execute([$idzona, $nombre, $direccion, $telefono, $descripcion, $id]);
    $mensaje = "✅ Salón actualizado correctamente.";
}

// Listar salones
$stmt = $conn->prepare("SELECT s.IdSalon, s.Nombre, s.Direccion, s.Telefono, s.Descripcion, z.Nombre AS ZonaNombre 
                        FROM Salones s 
                        INNER JOIN Zonas z ON s.IdZona = z.IdZona
                        ORDER BY s.IdSalon ASC");
$stmt->execute();
$salones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Listar zonas
$zonasStmt = $conn->prepare("SELECT * FROM Zonas ORDER BY Nombre ASC");
$zonasStmt->execute();
$zonas = $zonasStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Salones</title>
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
        color: #FFD700; /* texto dorado */
        margin-bottom: 20px; 
        border-radius: 6px; 
        overflow: hidden; 
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
    }

    th, td { 
        border: 1px solid #FFD700; /* borde dorado */
        padding: 10px; 
        text-align: left; 
    }

    th { 
        background: #FFD700; /* cabecera dorada */
        color: #000000; /* texto negro */
    }

    form { 
        background: #000000; /* fondo del formulario negro */
        padding: 20px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
    }

    input, select, textarea { 
        width: 100%; 
        padding: 8px; 
        margin: 5px 0; 
        border-radius: 5px; 
        border: 1px solid #FFD700; /* borde dorado */
        background: #111111; 
        color: #FFD700;
    }

    button { 
        padding: 10px 15px; 
        background: #FFD700; /* botón dorado */
        color: #000000; /* texto negro */
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-weight: bold;
        transition: all 0.3s ease;
    }

    button:hover { 
        background: #FFC300; /* dorado intenso */
        color: #000000; 
        transform: scale(1.05);
    }

    .mensaje { 
        color: #FFD700; 
        margin-bottom: 15px; 
        font-weight: bold; 
        text-shadow: 1px 1px 2px #000000;
    }

    a { 
        color: #FFD700; 
        text-decoration: none; 
        margin-right: 10px; 
        font-weight: bold;
    }

    a:hover { 
        text-decoration: underline; 
        color: #FFC300; /* dorado intenso */
    }
</style>

</head>
<body>

<header>
    <h1>Gestión de Salones</h1>
    <a href="admin_index.php" style="color:white;">⬅ Volver al Panel</a>
</header>

<main>

<?php if($mensaje): ?>
    <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<?php if($salonEditar): ?>
    <h2>Editar Salón</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= $salonEditar['IdSalon'] ?>">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($salonEditar['Nombre']) ?>" required>
        <label>Dirección:</label>
        <input type="text" name="direccion" value="<?= htmlspecialchars($salonEditar['Direccion']) ?>" required>
        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($salonEditar['Telefono']) ?>">
        <label>Zona:</label>
        <select name="idzona" required>
            <?php foreach($zonas as $zona): ?>
                <option value="<?= $zona['IdZona'] ?>" <?= $zona['IdZona'] == $salonEditar['IdZona'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($zona['Nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>Descripción:</label>
        <textarea name="descripcion"><?= htmlspecialchars($salonEditar['Descripcion']) ?></textarea>
        <button type="submit" name="actualizar">Guardar Cambios</button>
        <a href="gestion_salones.php">Cancelar</a>
    </form>
<?php else: ?>
    <h2>Agregar Nuevo Salón</h2>
    <form method="post">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        <label>Dirección:</label>
        <input type="text" name="direccion" required>
        <label>Teléfono:</label>
        <input type="text" name="telefono">
        <label>Zona:</label>
        <select name="idzona" required>
            <option value="">Selecciona una zona</option>
            <?php foreach($zonas as $zona): ?>
                <option value="<?= $zona['IdZona'] ?>"><?= htmlspecialchars($zona['Nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Descripción:</label>
        <textarea name="descripcion"></textarea>
        <button type="submit" name="agregar">Agregar Salón</button>
    </form>
<?php endif; ?>

<h2>Listado de Salones</h2>
<table>
    <thead>
        <tr>
            <!-- ID oculto -->
            <th style="display:none;">ID</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Zona</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($salones as $salon): ?>
        <tr>
            <td style="display:none;"><?= $salon['IdSalon'] ?></td>
            <td><?= htmlspecialchars($salon['Nombre']) ?></td>
            <td><?= htmlspecialchars($salon['Direccion']) ?></td>
            <td><?= htmlspecialchars($salon['Telefono']) ?></td>
            <td><?= htmlspecialchars($salon['ZonaNombre']) ?></td>
            <td><?= htmlspecialchars($salon['Descripcion']) ?></td>
            <td>
                <a href="gestion_salones.php?editar=<?= $salon['IdSalon'] ?>">✏️ Editar</a>
                <a href="gestion_salones.php?eliminar=<?= $salon['IdSalon'] ?>" onclick="return confirm('¿Seguro quieres eliminar este salón?')">🗑️ Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>
