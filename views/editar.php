<?php
$db = require_once '../config/db.php';

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';
    
    if ($action !== 'update') {
        throw new Exception('Acción no válida');
    }

    // Obtener el ID del usuario
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de usuario no proporcionado');
    }

    // Verificar si el usuario existe
    $checkStmt = $db->prepare("SELECT * FROM usuarios WHERE ANEXO = ?");
    $checkStmt->execute([$id]);
    $usuario = $checkStmt->fetch();
    
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Construir la consulta de actualización dinámicamente
    $updateFields = [];
    $params = [];

    // Solo incluir los campos que se enviaron en la solicitud
    if (isset($_POST['apellido'])) {
        $updateFields[] = "APELLIDO = :apellido";
        $params[':apellido'] = $_POST['apellido'];
    }
    if (isset($_POST['nombre'])) {
        $updateFields[] = "NOMBRE = :nombre";
        $params[':nombre'] = $_POST['nombre'];
    }
    if (isset($_POST['ubicacion'])) {
        $updateFields[] = "UBICACION = :ubicacion";
        $params[':ubicacion'] = $_POST['ubicacion'];
    }
    if (isset($_POST['correo'])) {
        $updateFields[] = "CORREO = :correo";
        $params[':correo'] = $_POST['correo'];
    }
    if (isset($_POST['anexo']) && $_POST['anexo'] !== $id) {
        // Verificar que el nuevo anexo no exista
        $checkAnexo = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE ANEXO = ? AND ANEXO != ?");
        $checkAnexo->execute([$_POST['anexo'], $id]);
        if ($checkAnexo->fetchColumn() > 0) {
            throw new Exception('El número de anexo ya existe');
        }
        $updateFields[] = "ANEXO = :anexo";
        $params[':anexo'] = $_POST['anexo'];
    }

    // Si no hay campos para actualizar
    if (empty($updateFields)) {
        echo json_encode([
            'success' => true,
            'message' => 'No hay cambios para actualizar',
            'user' => $usuario
        ]);
        exit;
    }

    // Construir y ejecutar la consulta SQL
    $sql = "UPDATE usuarios SET " . implode(", ", $updateFields) . " WHERE ANEXO = :id";
    $params[':id'] = $id;

    $stmt = $db->prepare($sql);
    
    if ($stmt->execute($params)) {
        // Obtener los datos actualizados
        $stmtSelect = $db->prepare("SELECT * FROM usuarios WHERE ANEXO = ?");
        $stmtSelect->execute([$id]);
        $updatedUser = $stmtSelect->fetch();

        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => $updatedUser
        ]);
    } else {
        throw new Exception('Error al actualizar el usuario');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
