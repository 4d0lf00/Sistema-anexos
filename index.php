<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: views/login.php');
    exit;
}

// Verificar la IP del usuario para prevenir robo de sesión
if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    header('Location: views/login.php');
    exit;
}

// Verificar tiempo de inactividad (30 minutos)
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity'] > 1800)) {
    session_destroy();
    header('Location: views/login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

$db = require_once 'config/db.php';

// Consulta simple
$sql = "SELECT * FROM usuarios ORDER BY CAST(ANEXO AS UNSIGNED)";
$stmt = $db->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Headers de caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Gestión de Usuarios</title>
    <link rel="icon" type="image/png" href="./public/img/logo.png">
    <link rel="stylesheet" href="public/css/style.css?v=1.0" media="all">
    <script src="public/script/script.js?v=1.0" defer></script>
    <link rel="preload" href="./public/img/logo.png" as="image">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if (isset($_SESSION['mensaje_importacion'])): ?>
        <div class="alerta <?php echo $_SESSION['tipo_alerta']; ?>">
            <div class="alert-content">
                <div class="alert-icon"><?php echo $_SESSION['tipo_alerta'] === 'success' ? '✓' : '×'; ?></div>
                <div class="alert-text">
                    <h4><?php echo $_SESSION['mensaje_importacion']['titulo']; ?></h4>
                    <?php if ($_SESSION['tipo_alerta'] === 'success'): ?>
                        <p>Nuevos registros importados: <strong><?php echo $_SESSION['mensaje_importacion']['nuevos']; ?></strong></p>
                        <p>Registros existentes omitidos: <strong><?php echo $_SESSION['mensaje_importacion']['existentes']; ?></strong></p>
                        <p>Total procesados: <strong><?php echo $_SESSION['mensaje_importacion']['total']; ?></strong></p>
                    <?php else: ?>
                        <p><?php echo $_SESSION['mensaje_importacion']['mensaje']; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php 
        unset($_SESSION['mensaje_importacion']);
        unset($_SESSION['tipo_alerta']);
        ?>
    <?php endif; ?>
    <div class="container">
        <div class="header">
            <div class="title">
                <img src="./public/img/logo.png" alt="Logo" class="logo-header">
                <h1>Usuarios registrados</h1>
            </div>
            <div class="search-wrapper">
                <div class="search-container">
                    <input 
                        class="search-input" 
                        name="search" 
                        placeholder="Buscar usuario..." 
                        type="search" 
                        id="searchInput"
                        autocomplete="off"
                    >
                    <button class="clear-search" id="clearSearch" type="button">×</button>
                </div>
            </div>
            <div class="header-buttons">
                <button class="excel-btn" id="excelBtn">
                    <span>📊</span>
                    Excel
                </button>
                <button class="add-user-btn">
                    <span>+</span>
                    Agregar usuario
                </button>
                <a href="views/logout.php" class="logout-btn">
                    <span>🚪</span>
                    Cerrar sesión
                </a>
            </div>
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>Anexo</th>
                    <th>Apellido</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Correo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['ANEXO']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['APELLIDO']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['NOMBRE']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['UBICACION']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['CORREO']); ?></td>
                        <td class="actions">
                            <button class="edit-btn" data-id="<?php echo $usuario['ANEXO']; ?>">✏️</button>
                            <button class="delete-btn" data-id="<?php echo $usuario['ANEXO']; ?>">🗑️</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Primero van todos los modales -->
    <!-- Modal para editar/crear usuario -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Usuario</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="userForm" method="POST">
                <input type="hidden" name="action" id="action" value="update">
                <input type="hidden" name="id" id="userId">
                
                <div class="form-group">
                    <label for="anexo">Anexo</label>
                    <input type="text" id="anexo" name="anexo">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="ubicacion">Ubicación</label>
                    <input type="text" id="ubicacion" name="ubicacion" required>
                </div>
                <div class="form-group">
                    <label for="correo">Correo</label>
                    <input type="text" id="correo" name="correo">
                </div>
                <div class="modal-footer">
                    <button type="button" class="cancel-btn">Cancelar</button>
                    <button type="submit" class="save-btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal" id="deleteModal">
        <div class="modal-content card">
            <div class="card-content">
                <p class="card-heading">Eliminar Usuario?</p>
                <p class="card-description">Estas segura de querer eliminar este usuario?</p>
            </div>
            <div class="card-button-wrapper">
                <button class="card-button secondary" id="cancelDelete">Cancelar</button>
                <button class="card-button primary" id="confirmDelete">Eliminar</button>
            </div>
            <button class="exit-button" id="closeDeleteModal">
                <svg height="20px" viewBox="0 0 384 512">
                    <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Modal de Excel -->
    <div class="modal" id="excelModal">
        <div class="modal-content card">
            <svg xml:space="preserve" viewBox="0 0 24 24" width="50" height="50">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM9 19H7v-2h2v2zm0-4H7v-2h2v2zm0-4H7V9h2v2zm6 8h-4v-2h4v2zm0-4h-4v-2h4v2zm0-4h-4V9h4v2zm-1-3V2l6 6h-6z" fill="#1d6f42"/>
            </svg>
            <p class="cookieHeading">Opciones de Excel</p>
            <p class="cookieDescription">Seleccione una opción para gestionar los datos</p>

            <div class="buttonContainer">
                <a href="views/exportar_excel.php" class="acceptButton">Exportar</a>
                <label for="excelFile" class="declineButton">Importar</label>
            </div>
            
            <form id="importForm" action="views/importar_excel.php" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" 
                       id="excelFile" 
                       name="excel_file" 
                       accept=".xls,.html,.htm" 
                       onchange="this.form.submit()"
                       style="display: none;">
            </form>
            
            <button class="exit-button" id="closeExcelModal">
                <svg height="20px" viewBox="0 0 384 512">
                    <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Debug - Ver estructura de datos -->
    <div style="display:none">
    <?php
        echo "Tipo de \$usuarios: " . gettype($usuarios) . "<br>";
        echo "Contenido de \$usuarios:<br>";
        print_r($usuarios);
        
        // Ver la consulta SQL que se está ejecutando
        echo "<br>SQL ejecutado: " . $sql . "<br>";
        
        // Ver si hay errores en la consulta
        if ($stmt->errorInfo()[0] !== '00000') {
            echo "Error en la consulta: ";
            print_r($stmt->errorInfo());
        }
    ?>
    </div>

    <!-- Debug info -->
    <pre style="display: none">
    <?php
        echo "Tipo de datos de \$usuarios: " . gettype($usuarios) . "\n";
        echo "Número de registros: " . count($usuarios) . "\n";
        echo "Primera fila:\n";
        if (!empty($usuarios)) {
            print_r(reset($usuarios));
        }
        echo "\nConsulta SQL:\n" . $sql . "\n";
        
        // Ver si hay errores en la consulta
        echo "\nEstado de la consulta:\n";
        print_r($stmt->errorInfo());
        
        // Ver el JSON generado
        echo "\nJSON generado:\n";
        echo htmlspecialchars($usuariosJSON);
    ?>
    </pre>

    <!-- Los scripts van al final -->
    <script>
        // Inicializar datos
        window.allUsers = <?php echo json_encode(array_values($usuarios)); ?>;
    </script>
    <script src="public/script/script.js"></script>
</body>
</html>