<?php
session_start();

// Prevenir el almacenamiento en caché del navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Si ya está autenticado, redirigir al index
if (isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inicio de sesión</title>
    <!-- Rutas corregidas -->
    <link rel="stylesheet" href="../public/css/login.css?v=1.0" media="all">
    <link rel="stylesheet" href="../public/css/spinner.css?v=1.0" media="all">
    <link rel="preload" href="../public/img/logo.png" as="image">
    <link rel="preload" href="../public/img/logo2.png" as="image">
    <link rel="icon" type="image/png" href="../public/img/logo.png">
</head>
<body>
    <div class="spinner-container">
        <div class="spinner">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <div class="container">
        <div class="login-box">
            <img src="../public/img/logo2.png" alt="Logo" class="login-logo">
            <p>Inicio sesion</p>
            <form id="loginForm" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="user-box">
                    <input required name="email" type="text" autocomplete="off">
                    <label>Usuario</label>
                </div>
                <div class="user-box">
                    <input required name="password" type="password" autocomplete="off">
                    <label>Contraseña</label>
                </div>
                <a href="#" id="submitBtn">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    Aceptar
                </a>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const spinnerContainer = document.querySelector('.spinner-container');

            let isSubmitting = false;

            async function handleSubmit(e) {
                e.preventDefault();
                if (isSubmitting) return;
                
                isSubmitting = true;
                spinnerContainer.style.display = 'flex';
                
                try {
                    const formData = new FormData(form);
                    
                    // Debug - ver qué datos se están enviando
                    console.log('Enviando datos:', {
                        email: formData.get('email'),
                        password: formData.get('password')
                    });
                    
                    const response = await fetch('procesar_login.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Debug - ver status de la respuesta
                    console.log('Status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('La respuesta no es JSON válido!');
                    }
                    
                    const data = await response.json();
                    console.log('Respuesta:', data);
                    
                    if (data.success) {
                        setTimeout(() => {
                            spinnerContainer.style.display = 'none';
                            window.location.href = '../index.php';
                        }, 2000);
                    } else {
                        spinnerContainer.style.display = 'none';
                        alert(data.error || 'Datos incorrectos. Por favor, intente nuevamente.');
                        form.reset();
                    }
                } catch (error) {
                    console.error('Error completo:', error);
                    spinnerContainer.style.display = 'none';
                    alert('Error al procesar la solicitud: ' + error.message);
                } finally {
                    isSubmitting = false;
                }
            }

            submitBtn.addEventListener('click', handleSubmit);
            form.addEventListener('submit', handleSubmit);
            form.reset();
        });
    </script>
</body>
</html>
