<?php
session_start();
require '../conexion.php';

// Verificar si hay sesión activa y admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";

// =================== LISTAR SALONES PARA FORMULARIOS ===================
$salonesStmt = $conn->query("SELECT IdSalon, Nombre FROM Salones ORDER BY Nombre ASC");
$salones = $salonesStmt->fetchAll(PDO::FETCH_ASSOC);

// Prefijos para nombres automáticos según salón
$prefijos = [
    'Divertiland' => 'Divertiland',
    'DulceEncanto' => 'DulceEncanto',
    'HakunaMatata' => 'HakunaMatata'
];

// =================== AGREGAR FOTO ===================
if (isset($_POST['agregar'])) {
    $idSalon = $_POST['idSalon'];
    $archivo = $_FILES['foto']['name'];
    $rutaTemporal = $_FILES['foto']['tmp_name'];

    if ($idSalon && $archivo) {
        // Obtener nombre del salón
        $stmtSalon = $conn->prepare("SELECT Nombre FROM Salones WHERE IdSalon = ?");
        $stmtSalon->execute([$idSalon]);
        $salon = $stmtSalon->fetch(PDO::FETCH_ASSOC);
        $nombreSalon = $salon['Nombre'];

        $prefijo = isset($prefijos[$nombreSalon]) ? $prefijos[$nombreSalon] : 'Foto';

        // Primero insertamos un registro vacío para obtener el IdFoto
        $stmtInsert = $conn->prepare("INSERT INTO Fotos (IdSalon, UrlFoto) VALUES (?, '')");
        $stmtInsert->execute([$idSalon]);
        $idFoto = $conn->lastInsertId();

        // Generar nombre único con el IdFoto
        $extension = pathinfo($archivo, PATHINFO_EXTENSION);
        $nombreFinal = $prefijo . "_ID" . $idFoto . "." . $extension;
        $destino = "../Fotos/" . $nombreFinal;

        if (move_uploaded_file($rutaTemporal, $destino)) {
            // Actualizar UrlFoto ya con el archivo
            $stmtUpdate = $conn->prepare("UPDATE Fotos SET UrlFoto = ? WHERE IdFoto = ?");
            $stmtUpdate->execute(["Fotos/$nombreFinal", $idFoto]);
            $mensaje = "Foto agregada correctamente.";
        } else {
            // Si falla, eliminamos el registro creado
            $conn->prepare("DELETE FROM Fotos WHERE IdFoto = ?")->execute([$idFoto]);
            $mensaje = "Error al subir la foto.";
        }
    } else {
        $mensaje = "Selecciona un salón y un archivo de imagen.";
    }
}

// =================== ELIMINAR FOTO ===================
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmtFile = $conn->prepare("SELECT UrlFoto FROM Fotos WHERE IdFoto = ?");
    $stmtFile->execute([$id]);
    $fotoExistente = $stmtFile->fetch(PDO::FETCH_ASSOC);
    if ($fotoExistente && file_exists("../" . $fotoExistente['UrlFoto'])) {
        unlink("../" . $fotoExistente['UrlFoto']); // eliminar archivo físico
    }
    $stmt = $conn->prepare("DELETE FROM Fotos WHERE IdFoto = ?");
    $stmt->execute([$id]);
    $mensaje = "Foto eliminada correctamente.";
}

// =================== EDITAR FOTO ===================
if (isset($_POST['editar'])) {
    $id = $_POST['idFoto'];
    $idSalon = $_POST['idSalon'];
    $archivo = $_FILES['foto']['name'];
    $rutaTemporal = $_FILES['foto']['tmp_name'];

    if ($archivo) {
        // Eliminar foto anterior
        $stmtOld = $conn->prepare("SELECT UrlFoto FROM Fotos WHERE IdFoto = ?");
        $stmtOld->execute([$id]);
        $fotoOld = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if ($fotoOld && file_exists("../" . $fotoOld['UrlFoto'])) {
            unlink("../" . $fotoOld['UrlFoto']);
        }

        // Obtener nombre del salón
        $stmtSalon = $conn->prepare("SELECT Nombre FROM Salones WHERE IdSalon = ?");
        $stmtSalon->execute([$idSalon]);
        $salon = $stmtSalon->fetch(PDO::FETCH_ASSOC);
        $nombreSalon = $salon['Nombre'];
        $prefijo = isset($prefijos[$nombreSalon]) ? $prefijos[$nombreSalon] : 'Foto';

        // Nombre único con IdFoto
        $extension = pathinfo($archivo, PATHINFO_EXTENSION);
        $nombreFinal = $prefijo . "_ID" . $id . "." . $extension;
        $destino = "../Fotos/" . $nombreFinal;

        move_uploaded_file($rutaTemporal, $destino);

        $stmt = $conn->prepare("UPDATE Fotos SET IdSalon = ?, UrlFoto = ? WHERE IdFoto = ?");
        $stmt->execute([$idSalon, "Fotos/$nombreFinal", $id]);
    } else {
        $stmt = $conn->prepare("UPDATE Fotos SET IdSalon = ? WHERE IdFoto = ?");
        $stmt->execute([$idSalon, $id]);
    }
    $mensaje = "Foto actualizada correctamente.";
}

// =================== LISTAR FOTOS ===================
$stmt = $conn->query("
    SELECT f.IdFoto, s.Nombre AS Salon, f.UrlFoto
    FROM Fotos f
    INNER JOIN Salones s ON f.IdSalon = s.IdSalon
    ORDER BY f.IdFoto ASC
");
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Fotos - Admin</title>
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

.mensaje { 
    color:#FFD700; 
    margin-bottom:15px; 
    font-weight:bold; 
    text-shadow: 1px 1px 2px #000000;
}

img { 
    max-width:100px; 
    border:2px solid #FFD700; 
    border-radius:6px;
}

form { 
    background:#000000; /* fondo negro para formulario */
    padding:15px; 
    border-radius:8px; 
    margin-bottom:20px; 
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
}

input, select { 
    padding:8px; 
    margin:5px 0; 
    width:100%; 
    background:#111111; /* fondo negro */
    color:#FFD700; /* texto dorado */
    border:1px solid #FFD700; 
    border-radius:6px;
}

button { 
    background:#FFD700; /* dorado */
    color:#000000; 
    padding:10px; 
    border:none; 
    border-radius:6px; 
    cursor:pointer; 
    margin-top:5px;
    font-weight:bold;
    transition: all 0.3s ease;
}

button:hover { 
    background:#FFC300; /* dorado intenso */
    transform: scale(1.05);
}
</style>

</head>
<body>

<header>
<h1>Gestión de Fotos</h1>
<a href="admin_index.php" style="color:white;">⬅ Volver al Panel</a>
</header>

<main>

<?php if($mensaje): ?>
<p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<!-- FORMULARIO AGREGAR FOTO -->
<form method="post" enctype="multipart/form-data">
    <h3>Agregar Nueva Foto</h3>
    <label>Seleccionar Salón:</label>
    <select name="idSalon" required>
        <option value="">-- Selecciona un salón --</option>
        <?php foreach($salones as $salon): ?>
            <option value="<?= $salon['IdSalon'] ?>"><?= htmlspecialchars($salon['Nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Archivo de Imagen:</label>
    <input type="file" name="foto" accept="image/*" required>
    <button type="submit" name="agregar">Agregar Foto</button>
</form>

<!-- TABLA DE FOTOS -->
<table>
<thead>
<tr>
<th>ID</th>
<th>Salón</th>
<th>Foto</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($fotos as $foto): ?>
<tr>
<td><?= $foto['IdFoto'] ?></td>
<td><?= htmlspecialchars($foto['Salon']) ?></td>
<td><img src="../<?= htmlspecialchars($foto['UrlFoto']) ?>" alt="Foto"></td>
<td>
<!-- Formulario para EDITAR -->
<form method="post" enctype="multipart/form-data" style="display:inline-block;">
    <input type="hidden" name="idFoto" value="<?= $foto['IdFoto'] ?>">
    <label>Cambiar Salón:</label>
    <select name="idSalon" required>
        <?php foreach($salones as $salon): ?>
            <option value="<?= $salon['IdSalon'] ?>" <?= $salon['Nombre']==$foto['Salon']?'selected':'' ?>>
                <?= htmlspecialchars($salon['Nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label>Reemplazar Foto:</label>
    <input type="file" name="foto" accept="image/*">
    <button type="submit" name="editar">Editar</button>
</form>
<!-- Botón Eliminar -->
<a href="gestion_fotos.php?eliminar=<?= $foto['IdFoto'] ?>" onclick="return confirm('¿Seguro quieres eliminar esta foto?')">Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</main>
</body>
</html>
