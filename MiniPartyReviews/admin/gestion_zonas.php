<?php
session_start();
require '../conexion.php';

// Verificar sesión admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";

// Agregar nueva zona
if (isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $ciudad = trim($_POST['ciudad']);
    $descripcion = trim($_POST['descripcion']);

    if ($nombre && $ciudad) {
        $stmt = $conn->prepare("INSERT INTO Zonas (Nombre, Ciudad, Descripcion) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $ciudad, $descripcion]);
        $mensaje = "Zona agregada correctamente.";
    } else {
        $mensaje = "Nombre y Ciudad son obligatorios.";
    }
}

// Editar zona
if (isset($_POST['editar'])) {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $ciudad = trim($_POST['ciudad']);
    $descripcion = trim($_POST['descripcion']);

    if ($nombre && $ciudad) {
        $stmt = $conn->prepare("UPDATE Zonas SET Nombre = ?, Ciudad = ?, Descripcion = ? WHERE IdZona = ?");
        $stmt->execute([$nombre, $ciudad, $descripcion, $id]);
        $mensaje = "Zona actualizada correctamente.";
    } else {
        $mensaje = "Nombre y Ciudad son obligatorios.";
    }
}

// Eliminar zona
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM Zonas WHERE IdZona = ?");
    $stmt->execute([$id]);
    $mensaje = "Zona eliminada correctamente.";
}

// Si se está editando, obtener los datos
$editarZona = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM Zonas WHERE IdZona = ?");
    $stmt->execute([$id]);
    $editarZona = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar todas las zonas
$stmt = $conn->query("SELECT * FROM Zonas ORDER BY IdZona ASC");
$zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Zonas - Admin</title>
<style>
body { 
    font-family: Arial; 
    background: #111111; /* fondo negro */
    margin:0; 
    padding:0; 
    color: #FFD700; /* texto dorado */
}

header { 
    background: #000000; /* header negro */
    color: #FFD700; /* texto dorado */
    padding: 20px; 
    text-align:center; 
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
}

main { 
    padding:20px; 
}

table { 
    width:100%; 
    border-collapse: collapse; 
    background:#000000; /* tabla negra */
    margin-bottom:20px; 
    color:#FFD700; /* texto dorado */
    border-radius:6px; 
    overflow:hidden;
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
}

th, td { 
    border:1px solid #FFD700; /* borde dorado */
    padding:10px; 
    text-align:left; 
}

th { 
    background:#FFD700; /* cabecera dorada */
    color:#000000; /* texto negro */
}

a { 
    color:#FFD700; 
    text-decoration:none; 
    font-weight:bold;
}

a:hover { 
    text-decoration:underline; 
    color:#FFC300; /* dorado intenso */
}

form { 
    background:#000000; /* formulario negro */
    padding:15px; 
    margin-bottom:20px; 
    border-radius:8px; 
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
}

input, textarea, button { 
    padding:8px; 
    margin:5px 0; 
    width:100%; 
    background:#111111; /* fondo negro para inputs */
    color:#FFD700; /* texto dorado */
    border:1px solid #FFD700; 
    border-radius:5px;
}

button { 
    background:#FFD700; /* dorado */
    color:#000000; 
    border:none; 
    cursor:pointer; 
    font-weight:bold;
    transition: all 0.3s ease;
}

button:hover { 
    background:#FFC300; /* dorado intenso */
    color:#000000; 
    transform: scale(1.05);
}

.mensaje { 
    color:#FFD700; 
    margin-bottom:15px; 
    font-weight:bold; 
    text-shadow: 1px 1px 2px #000000;
}
</style>

</head>
<body>

<header>
<h1>Gestión de Zonas</h1>
<a href="admin_index.php" style="color:white;">⬅ Volver al Panel</a>
</header>

<main>

<?php if($mensaje): ?>
<p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<!-- Formulario agregar/editar -->
<form method="post">
    <h3><?= $editarZona ? "Editar Zona" : "Agregar Nueva Zona" ?></h3>
    <input type="hidden" name="id" value="<?= $editarZona['IdZona'] ?? '' ?>">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($editarZona['Nombre'] ?? '') ?>" required>
    <label>Ciudad:</label>
    <input type="text" name="ciudad" value="<?= htmlspecialchars($editarZona['Ciudad'] ?? '') ?>" required>
    <label>Descripción:</label>
    <textarea name="descripcion"><?= htmlspecialchars($editarZona['Descripcion'] ?? '') ?></textarea>
    <button type="submit" name="<?= $editarZona ? 'editar' : 'agregar' ?>"><?= $editarZona ? 'Actualizar Zona' : 'Agregar Zona' ?></button>
</form>

<!-- Tabla de zonas -->
<table>
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Ciudad</th>
<th>Descripción</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($zonas as $zona): ?>
<tr>
<td><?= $zona['IdZona'] ?></td>
<td><?= htmlspecialchars($zona['Nombre']) ?></td>
<td><?= htmlspecialchars($zona['Ciudad']) ?></td>
<td><?= htmlspecialchars($zona['Descripcion']) ?></td>
<td>
<a href="?editar=<?= $zona['IdZona'] ?>">Editar</a> |
<a href="?eliminar=<?= $zona['IdZona'] ?>" onclick="return confirm('¿Seguro quieres eliminar esta zona?')">Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</main>
</body>
</html>
