<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = $_POST['email'];
        $password = $_POST['password'];

        // Consulta a la tabla acces
        $sql = "SELECT * FROM acces WHERE nombre = ? AND password = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $password]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['tipo'] == 1) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['last_login'] = time();
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
            $response['success'] = true;
        } else {
            $response['error'] = 'Credenciales incorrectas';
        }
    } catch(PDOException $e) {
        $response['error'] = "Error en el servidor";
    }
}

echo json_encode($response); 