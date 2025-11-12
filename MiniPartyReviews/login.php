<?php
session_start();
require 'conexion.php'; // archivo en la raíz

// Si ya hay sesión activa de admin, redirigir al panel admin
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header("Location: admin/admin_index.php");
    exit;
}

$error = "";

// Procesar envío del formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);

    if ($nombre !== "" && $correo !== "") {
        try {
            // Consulta segura usando prepared statements
            $stmt = $conn->prepare("SELECT IdUsuario, Nombre, Rol FROM Usuarios WHERE Nombre = ? AND Correo = ? AND Rol = 'admin' LIMIT 1");
            $stmt->execute([$nombre, $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Crear sesión
                $_SESSION['id'] = $usuario['IdUsuario'];
                $_SESSION['nombre'] = $usuario['Nombre'];
                $_SESSION['rol'] = $usuario['Rol'];

                // Redirigir al index del admin dentro de la carpeta admin
                header("Location: admin/admin_index.php");
                exit;
            } else {
                $error = "Usuario o correo incorrecto, o no tienes permisos de administrador.";
            }
        } catch (PDOException $e) {
            $error = "Error en la conexión a la base de datos.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Mini Party Reviews</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e0f7f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            width: 360px;
            text-align: center;
        }
        h2 { color: #1abc9c; margin-bottom: 20px; }
        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            background: #1abc9c;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 95%;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover { background: #16a085; }
        .error { color: red; margin-top: 10px; font-size: 14px; }
        a {
            display: block;
            margin-top: 15px;
            color: #1abc9c;
            text-decoration: none;
            font-size: 14px;
        }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Acceso Administrador</h2>
        <form method="post" action="">
            <input type="text" name="nombre" placeholder="Nombre de usuario" required>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <button type="submit">Ingresar</button>
        </form>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <a href="index.php">⬅ Volver al inicio</a>
    </div>
</body>
</html>
