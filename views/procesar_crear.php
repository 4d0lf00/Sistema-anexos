<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asignar el resultado del require a la variable $db
$db = require_once __DIR__ . '/../config/db.php';

if (!$db) {
    error_log("Error: No se pudo conectar a la base de datos");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

if ($db) {
    error_log("Conexión a la base de datos establecida correctamente");
} else {
    error_log("Error: No se pudo establecer la conexión a la base de datos");
}

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Debug: Ver los datos que llegan
    error_log("POST data: " . print_r($_POST, true));

    // Validar que todos los campos requeridos estén presentes
    $requiredFields = ['anexo', 'apellido', 'nombre', 'ubicacion', 'correo'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("El campo {$field} es requerido");
        }
    }

    // Obtener los datos del formulario
    $anexo = trim($_POST['anexo']);
    $apellido = trim($_POST['apellido']);
    $nombre = trim($_POST['nombre']);
    $ubicacion = trim($_POST['ubicacion']);
    $correo = trim($_POST['correo']);

    // Debug: Ver los datos procesados
    error_log("Datos procesados: anexo=$anexo, apellido=$apellido, nombre=$nombre, ubicacion=$ubicacion, correo=$correo");

    // Verificar si el anexo ya existe
    $checkSql = "SELECT COUNT(*) FROM usuarios WHERE ANEXO = :anexo";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->bindValue(':anexo', $anexo, PDO::PARAM_STR);
    $checkStmt->execute();
    
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception('El anexo ya existe');
    }

    // Preparar la consulta de inserción
    $sql = "INSERT INTO usuarios (ANEXO, APELLIDO, NOMBRE, UBICACION, CORREO) 
            VALUES (:anexo, :apellido, :nombre, :ubicacion, :correo)";
    
    $stmt = $db->prepare($sql);
    
    // Debug: Ver la consulta SQL con los valores
    $params = [
        ':anexo' => $anexo,
        ':apellido' => $apellido,
        ':nombre' => $nombre,
        ':ubicacion' => $ubicacion,
        ':correo' => $correo
    ];
    error_log("SQL: $sql");
    error_log("Params: " . print_r($params, true));

    $stmt->bindValue(':anexo', $anexo, PDO::PARAM_STR);
    $stmt->bindValue(':apellido', $apellido, PDO::PARAM_STR);
    $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindValue(':ubicacion', $ubicacion, PDO::PARAM_STR);
    $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'user' => [
                'ANEXO' => $anexo,
                'APELLIDO' => $apellido,
                'NOMBRE' => $nombre,
                'UBICACION' => $ubicacion,
                'CORREO' => $correo
            ]
        ]);
    } else {
        $error = $stmt->errorInfo();
        throw new Exception('Error al crear el usuario: ' . $error[2]);
    }

} catch (Exception $e) {
    error_log("Error en procesar_crear.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 