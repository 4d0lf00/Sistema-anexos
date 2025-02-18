<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Encuentra la ruta correcta al autoload.php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
} else {
    die('Por favor, ejecuta "composer install" en la raíz del proyecto');
}

require_once '../config/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    try {
        // Leer el contenido del archivo
        $contenido = file_get_contents($_FILES['excel_file']['tmp_name']);
        
        // Crear un DOMDocument para parsear el HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($contenido);
        
        // Obtener todas las filas de la tabla
        $rows = $dom->getElementsByTagName('tr');
        
        // Eliminamos el TRUNCATE ya que no queremos borrar la tabla
        // $db->query("TRUNCATE TABLE usuarios");
        
        // Modificamos la consulta de inserción para usar ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO usuarios (ANEXO, APELLIDO, NOMBRE, UBICACION, CORREO) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                APELLIDO = VALUES(APELLIDO),
                NOMBRE = VALUES(NOMBRE),
                UBICACION = VALUES(UBICACION),
                CORREO = VALUES(CORREO)";
        $stmt = $db->prepare($sql);
        
        $registrosImportados = 0;
        $registrosExistentes = 0;
        
        // Empezamos desde 1 para saltar el encabezado
        for ($i = 1; $i < $rows->length; $i++) {
            $row = $rows->item($i);
            $cells = $row->getElementsByTagName('td');
            
            if ($cells->length >= 5) {
                $anexo = trim($cells->item(0)->nodeValue);
                $apellido = trim($cells->item(1)->nodeValue);
                $nombre = trim($cells->item(2)->nodeValue);
                $ubicacion = trim($cells->item(3)->nodeValue);
                $correo = trim($cells->item(4)->nodeValue);
                
                // Formatear campos para mantener consistencia con el diseño
                $anexo = mb_strtoupper(trim($anexo));
                if ($anexo === '') {
                    continue; // Saltamos registros sin anexo
                }

                // Normalizar caracteres especiales
                $apellido = mb_convert_encoding(trim($apellido), 'UTF-8', 'UTF-8');
                $nombre = mb_convert_encoding(trim($nombre), 'UTF-8', 'UTF-8');
                $ubicacion = mb_convert_encoding(trim($ubicacion), 'UTF-8', 'UTF-8');
                
                // Convertir a mayúsculas después de normalizar
                $apellido = mb_strtoupper($apellido);
                $nombre = mb_strtoupper($nombre);
                $ubicacion = mb_strtoupper($ubicacion);

                // Reemplazar específicamente SANTIBAÃEZ por SANTIBAÑEZ
                $apellido = str_replace('SANTIBAÃEZ', 'SANTIBAÑEZ', $apellido);

                // Limpiar correo
                $correo = strtolower(trim($correo));
                if ($correo === '@' || $correo === 'sin correo' || $correo === '@munimelipilla' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $correo = '@';
                }

                // Verificar si el registro ya existe en la base de datos
                $stmt->execute([$anexo, $apellido, $nombre, $ubicacion, $correo]);
                $registrosImportados++;
            }
        }
        
        // Después de importar todos los registros
        if ($registrosImportados > 0) {
            // Actualizar correos vacíos
            $sqlUpdateCorreos = "UPDATE usuarios SET CORREO = '@' WHERE CORREO = '' OR CORREO IS NULL";
            $db->query($sqlUpdateCorreos);
            
            $_SESSION['tipo_alerta'] = "success";
            $_SESSION['mensaje_importacion'] = [
                'titulo' => '¡Importación Completada!',
                'nuevos' => $registrosImportados,
                'existentes' => $registrosExistentes,
                'total' => $registrosImportados + $registrosExistentes
            ];
        }
        
    } catch (Exception $e) {
        $_SESSION['tipo_alerta'] = "error";
        $_SESSION['mensaje_importacion'] = [
            'titulo' => 'Error en la importación',
            'mensaje' => $e->getMessage()
        ];
    }
    
    // Redirigir de vuelta al index
    header('Location: ../index.php');
    exit;
}

// Si no es POST, redirigir al index
header('Location: ../index.php');
exit;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Lista de Usuarios</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .mensaje {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .resultado-importacion {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .resultado-importacion h4 {
            color: #28a745;
            margin: 0 0 10px 0;
            font-size: 1.2em;
        }
        .resultado-importacion ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .resultado-importacion li {
            padding: 5px 0;
            color: #666;
        }
        .resultado-importacion strong {
            color: #333;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Importar Lista de Usuarios</h2>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel_file">Seleccionar archivo:</label>
                <input type="file" name="excel_file" accept=".xls,.html,.htm" required>
            </div>
            <button type="submit" class="btn">Importar</button>
            <a href="../index.php" class="btn">Volver</a>
        </form>

        <div style="margin-top: 20px;">
            <h3>Instrucciones:</h3>
            <ol>
                <li>El archivo debe contener una tabla con las siguientes columnas en orden:
                    <ul>
                        <li>ANEXO</li>
                        <li>APELLIDO</li>
                        <li>NOMBRE</li>
                        <li>UBICACION</li>
                        <li>CORREO</li>
                    </ul>
                </li>
                <li>La primera fila debe contener los encabezados</li>
                <li>Se importarán todos los registros que tengan al menos uno de estos campos: ANEXO, APELLIDO o NOMBRE</li>
            </ol>
        </div>
    </div>
</body>
</html> 