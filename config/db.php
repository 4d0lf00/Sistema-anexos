<?php

$host = 'localhost';
$port = 3307;
$dbname = 'testing';
$user = 'root';
$password = '';

try {
    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
