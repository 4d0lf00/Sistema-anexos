<?php

$host = 'localhost';  // IP del servidor donde está MySQL
$dbname = 'intranet_xd';     // Nombre corregido de la base de datos
$user = 'intranet';  // Usuario de MySQL creado en CWP
$password = 'yGpsS5gpypne';  // Contraseña del usuario MySQL
$port = '3306';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        $options
    );
    return $db;
} catch (PDOException $e) {
    error_log("Error de conexión PDO: " . $e->getMessage());
    throw new Exception("Error crítico de base de datos");
}
?>
