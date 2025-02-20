<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$response = ['success' => false];

// Validación mejorada de CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Solicitud no válida']));
}

try {
    $nombre = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validación de entrada más robusta
    if (empty(trim($nombre)) || empty(trim($password))) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => 'Credenciales incompletas']));
    }

    // Consulta segura con nombre de tabla verificado
    $sql = "SELECT * FROM acces WHERE nombre = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$nombre]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verificación de contraseña (deberías implementar hash después)
        if ($password === $usuario['password']) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['last_login'] = time();
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
            
            $response['success'] = true;
        } else {
            $response['error'] = 'Credenciales incorrectas';
        }
    } else {
        $response['error'] = 'Usuario no encontrado';
    }
} catch (PDOException $e) {
    error_log("Error BD: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Error en el servidor']));
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Error inesperado']));
}

echo json_encode($response);
exit;