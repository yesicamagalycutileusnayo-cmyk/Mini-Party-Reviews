<?php
// Incluir la conexión
include 'conexion.php';

// Consulta de prueba
try {
    $stmt = $conn->query("SELECT * FROM Zonas");
    $zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ Conexión exitosa y datos de Zonas:<br>";

    if(count($zonas) == 0){
        echo "No hay registros en Zonas.";
    } else {
        foreach($zonas as $zona){
            echo "ID: " . $zona['IdZona'] . " - Nombre: " . $zona['Nombre'] . " - Ciudad: " . $zona['Ciudad'] . "<br>";
        }
    }

} catch (PDOException $e) {
    die("❌ Error en la consulta: " . $e->getMessage());
}
?>
