<?php
// Limpiar buffers y headers
while (ob_get_level()) ob_end_clean();
ob_start();
header('Content-Type: application/json');

// Habilitar logging detallado al inicio del archivo
error_log("Inicio de procesar_crear.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y verificar autenticación
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Acceso no autorizado']));
}

// Conexión a base de datos
require __DIR__ . '/../config/db.php';

try {
    // Validar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    // Validar campos requeridos (excepto correo)
    $camposRequeridos = ['anexo', 'apellido', 'nombre', 'ubicacion'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es obligatorio", 400);
        }
    }

    // Sanitizar datos
    $anexo = trim($_POST['anexo']);
    $apellido = trim($_POST['apellido']);
    $nombre = trim($_POST['nombre']);
    $ubicacion = trim($_POST['ubicacion']);
    $correo = trim($_POST['correo'] ?? '@');  // Valor por defecto '@' si no se proporciona

    // Debug logging
    error_log("Datos recibidos:");
    error_log("Anexo: " . $anexo);
    error_log("Apellido: " . $apellido);
    error_log("Nombre: " . $nombre);
    error_log("Ubicación: " . $ubicacion);
    error_log("Correo: " . $correo);

    // Validación adicional para campos vacíos después del trim
    if (empty($apellido)) {
        throw new Exception('El apellido no puede estar vacío', 400);
    }
    if (empty($nombre)) {
        throw new Exception('El nombre no puede estar vacío', 400);
    }
    if (empty($ubicacion)) {
        throw new Exception('La ubicación no puede estar vacía', 400);
    }
    // Removemos la validación estricta del correo
    if (empty($correo)) {
        $correo = '@';  // Asignar '@' si está vacío
    }

    // Validar formato numérico del anexo
    if (!preg_match('/^\d+$/', $anexo)) {
        throw new Exception('El anexo debe contener solo números', 400);
    }
    if ((int)$anexo <= 0) {
        throw new Exception('El anexo debe ser un número positivo', 400);
    }
    // Agregar logging para debug
    error_log("Intentando crear anexo: " . $anexo);

    $db->beginTransaction(); 

    // Verificar si es una actualización o creación
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        // Verificación de duplicados solo para nuevos registros
        $stmt = $db->prepare("SELECT 1 FROM usuarios WHERE ANEXO = ? LIMIT 1");
        $stmt->execute([$anexo]);
        if ($stmt->fetchColumn()) {
            $db->rollBack();
            throw new Exception('El número de anexo ya existe', 409);
        }

        // Insertar nuevo usuario
        $stmt = $db->prepare("
            INSERT INTO usuarios 
            (ANEXO, APELLIDO, NOMBRE, UBICACION, CORREO)
            VALUES (?, ?, ?, ?, ?)
        ");
    } else {
        // Actualizar usuario existente
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET APELLIDO = ?, NOMBRE = ?, UBICACION = ?, CORREO = ?
            WHERE ANEXO = ?
        ");
    }
    
    if ($action === 'create') {
        $stmt->execute([$anexo, $apellido, $nombre, $ubicacion, $correo]);
    } else {
        $stmt->execute([$apellido, $nombre, $ubicacion, $correo, $anexo]);
    }
    
    $db->commit();

    // Respuesta exitosa
    $response = [
        'success' => true,
        'message' => $action === 'create' ? 'Usuario creado exitosamente' : 'Usuario actualizado exitosamente',
        'data' => [
            'ANEXO' => $anexo,
            'APELLIDO' => $apellido,
            'NOMBRE' => $nombre,
            'UBICACION' => $ubicacion,
            'CORREO' => $correo
        ]
    ];

    // Asegurar que no haya salida previa
    ob_end_clean();
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    error_log("Error PDO: " . $e->getMessage());
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]));
} catch (Exception $e) {
    $db->rollBack(); // Revertir en caso de error
    ob_end_clean(); // Limpiar cualquier salida
    http_response_code($e->getCode() ?: 400);
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
}

ob_end_clean();