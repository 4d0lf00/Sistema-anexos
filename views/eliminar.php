<?php
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('ID no proporcionado');
    }

    $id = $_POST['id'];
    
    // Preparar la consulta de eliminación
    $sql = "DELETE FROM usuarios WHERE ANEXO = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    } else {
        throw new Exception('Error al eliminar el usuario');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
