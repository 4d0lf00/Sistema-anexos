<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

// Establecer headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Obtener los usuarios
$sql = "SELECT * FROM usuarios ORDER BY CAST(ANEXO AS UNSIGNED)";
$stmt = $db->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear el contenido del Excel
echo '<table border="1">';
// Encabezados
echo '<tr>';
echo '<th style="background-color: #1d6f42; color: white;">ANEXO</th>';
echo '<th style="background-color: #1d6f42; color: white;">APELLIDO</th>';
echo '<th style="background-color: #1d6f42; color: white;">NOMBRE</th>';
echo '<th style="background-color: #1d6f42; color: white;">UBICACIÓN</th>';
echo '<th style="background-color: #1d6f42; color: white;">CORREO</th>';
echo '</tr>';

// Datos
foreach($usuarios as $usuario) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($usuario['ANEXO']) . '</td>';
    echo '<td>' . htmlspecialchars($usuario['APELLIDO']) . '</td>';
    echo '<td>' . htmlspecialchars($usuario['NOMBRE']) . '</td>';
    echo '<td>' . htmlspecialchars($usuario['UBICACION']) . '</td>';
    echo '<td>' . htmlspecialchars($usuario['CORREO']) . '</td>';
    echo '</tr>';
}

echo '</table>';
exit; 