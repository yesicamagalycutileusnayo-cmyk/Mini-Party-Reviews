<?php
session_start();
require '../conexion.php';

// ---------------- SEGURIDAD ----------------
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";

// ---------------- CREAR NUEVO ADMIN ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevoAdmin'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);

    if (!$nombre || !$correo) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    } else {
        // Validar duplicados solo entre admins
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuarios WHERE Nombre = ? AND Rol = 'admin'");
        $stmt->execute([$nombre]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "⚠️ Ya existe un admin con ese nombre.";
        } else {
            $stmt = $conn->prepare("INSERT INTO Usuarios (Nombre, Correo, Rol) VALUES (?, ?, 'admin')");
            $stmt->execute([$nombre, $correo]);
            $mensaje = "Nuevo admin creado correctamente.";
        }
    }
}

// ---------------- EDITAR ADMIN ----------------
$editar = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];

    $stmt = $conn->prepare("SELECT * FROM Usuarios WHERE IdUsuario = ?");
    $stmt->execute([$id]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editar || $editar['Rol'] !== 'admin') {
        $editar = null; // Solo admins editables
    }
}

// ---------------- ACTUALIZAR ADMIN ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUsuario'])) {
    $id = (int)$_POST['idUsuario'];
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);

    if (!$nombre || !$correo) {
        $mensaje = "⚠️ Nombre y correo son obligatorios.";
    } else {
        // Validar duplicados solo entre admins
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuarios WHERE Nombre = ? AND Rol = 'admin' AND IdUsuario != ?");
        $stmt->execute([$nombre, $id]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "⚠️ Ya existe un admin con ese nombre.";
        } else {
            $stmt = $conn->prepare("UPDATE Usuarios SET Nombre = ?, Correo = ? WHERE IdUsuario = ?");
            $stmt->execute([$nombre, $correo, $id]);
            $mensaje = "Admin actualizado correctamente.";
            header("Location: gestion_usuarios.php");
            exit;
        }
    }
}

// ---------------- LISTAR USUARIOS ----------------
$stmt = $conn->query("SELECT * FROM Usuarios ORDER BY IdUsuario ASC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separar en admins y usuarios
$admins = array_filter($usuarios, fn($u) => $u['Rol'] === 'admin');
$usuariosNormales = array_filter($usuarios, fn($u) => $u['Rol'] === 'usuario');
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Usuarios - Admin</title>
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
    vertical-align: top; 
}

th { 
    background:#FFD700; /* cabecera dorada */
    color:#000000; /* texto negro */
}

a { 
    color:#FFD700; 
    text-decoration:none; 
    margin-right:5px; 
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

.form-editar, .form-nuevo { 
    background:#000000; /* fondo negro */
    padding:10px; 
    border-radius:8px; 
    margin-bottom:10px; 
    box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
}

.form-editar input, .form-nuevo input { 
    width: 100%; 
    margin-bottom:5px; 
    padding:5px; 
    background:#111111; 
    color:#FFD700; 
    border:1px solid #FFD700;
    border-radius:5px;
}

.btn { 
    padding:5px 10px; 
    border:none; 
    border-radius:5px; 
    cursor:pointer; 
    font-weight:bold;
    transition: all 0.3s ease;
}

.btn-actualizar { 
    background:#FFD700; /* dorado */
    color:#000000; 
}

.btn-actualizar:hover { 
    background:#FFC300; /* dorado intenso */
    color:#000000; 
    transform: scale(1.05);
}

.btn-cancelar { 
    background:#c0392b; /* rojo oscuro */
    color:#FFD700; 
}

.btn-cancelar:hover { 
    background:#e74c3c; /* rojo vivo */
    color:#000000; 
    transform: scale(1.05);
}
</style>

</head>
<body>

<header>
<h1>Gestión de Usuarios</h1>
<a href="admin_index.php" style="color:white;">⬅ Volver al Panel</a>
</header>

<main>

<?php if($mensaje): ?>
<p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<!-- FORMULARIO NUEVO ADMIN -->
<div class="form-nuevo">
<h3>Crear nuevo Admin</h3>
<form method="post">
<input type="hidden" name="nuevoAdmin" value="1">
<input type="text" name="nombre" placeholder="Nombre" required>
<input type="email" name="correo" placeholder="Correo" required>
<button type="submit" class="btn btn-actualizar">Crear Admin</button>
</form>
</div>

<!-- TABLA ADMINS -->
<h2>Admins</h2>
<table>
<thead>
<tr>
<th>Nombre</th>
<th>Correo</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($admins as $u): ?>
<tr>
<td>
<?php if($editar && $editar['IdUsuario'] == $u['IdUsuario']): ?>
<form method="post" class="form-editar">
<input type="hidden" name="idUsuario" value="<?= $u['IdUsuario'] ?>">
<input type="text" name="nombre" value="<?= htmlspecialchars($editar['Nombre']) ?>" required>
<input type="email" name="correo" value="<?= htmlspecialchars($editar['Correo']) ?>" required>
<button type="submit" class="btn btn-actualizar">Actualizar</button>
<a href="gestion_usuarios.php" class="btn btn-cancelar">Cancelar</a>
</form>
<?php else: ?>
<?= htmlspecialchars($u['Nombre']) ?>
<?php endif; ?>
</td>
<td><?= htmlspecialchars($u['Correo']) ?></td>
<td>
<?php if(!$editar || $editar['IdUsuario'] != $u['IdUsuario']): ?>
<a href="gestion_usuarios.php?editar=<?= $u['IdUsuario'] ?>">Editar</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- TABLA USUARIOS -->
<h2>Usuarios</h2>
<table>
<thead>
<tr>
<th>Nombre</th>
<th>Correo</th>
</tr>
</thead>
<tbody>
<?php foreach($usuariosNormales as $u): ?>
<tr>
<td><?= htmlspecialchars($u['Nombre']) ?></td>
<td><?= htmlspecialchars($u['Correo']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</main>
</body>
</html>
