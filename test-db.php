<?php
include 'config/db.php'; // Ajusta la ruta si es necesario

try {
    $db->query("SELECT 1");
    echo "✅ Conexión exitosa a MySQL";
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>
