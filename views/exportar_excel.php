<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

// Configurar headers para UTF-8 y Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d') . '.xls"');
header("Content-Transfer-Encoding: binary");
header('Pragma: no-cache');
header('Expires: 0');

// Agregar BOM para UTF-8
echo chr(239) . chr(187) . chr(191);

// Obtener los usuarios ordenados por ubicación
$sql = "SELECT * FROM usuarios ORDER BY UBICACION ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear el contenido del Excel con codificación UTF-8
echo '<html>';
echo '<head><meta charset="UTF-8"></head>';
echo '<body>';
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
    echo '<td>' . mb_convert_encoding(htmlspecialchars($usuario['ANEXO']), 'UTF-8', 'UTF-8') . '</td>';
    echo '<td>' . mb_convert_encoding(htmlspecialchars($usuario['APELLIDO']), 'UTF-8', 'UTF-8') . '</td>';
    echo '<td>' . mb_convert_encoding(htmlspecialchars($usuario['NOMBRE']), 'UTF-8', 'UTF-8') . '</td>';
    echo '<td>' . mb_convert_encoding(htmlspecialchars($usuario['UBICACION']), 'UTF-8', 'UTF-8') . '</td>';
    echo '<td>' . mb_convert_encoding(htmlspecialchars($usuario['CORREO']), 'UTF-8', 'UTF-8') . '</td>';
    echo '</tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';
exit;