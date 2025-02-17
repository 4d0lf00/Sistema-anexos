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
    <title>Inicio de sesion</title>
    <link rel="stylesheet" href="/crud/public/css/login.css?v=1.0" media="all">
    <link rel="stylesheet" href="/crud/public/css/spinner.css?v=1.0" media="all">
    <link rel="preload" href="/crud/public/img/logo.png" as="image">
    <link rel="preload" href="/crud/public/img/logo2.png" as="image">
    <link rel="icon" type="image/png" href="/crud/public/img/logo.png">
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
            <img src="/crud/public/img/logo2.png" alt="Logo" class="login-logo">
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
            spinnerContainer.style.display = 'flex'; // Mostrar el spinner
            
            try {
                const formData = new FormData(form);
                const response = await fetch('procesar_login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Esperar 2 segundos antes de redirigir
                    setTimeout(() => {
                        spinnerContainer.style.display = 'none'; // Ocultar el spinner
                        window.location.href = '../index.php'; // Redirigir
                    }, 2000); // 2000 milisegundos = 2 segundos
                } else {
                    spinnerContainer.style.display = 'none'; // Ocultar el spinner
                    alert('Datos incorrectos. Por favor, intente nuevamente.');
                    form.reset();
                }
            } catch (error) {
                console.error('Error:', error);
                spinnerContainer.style.display = 'none'; // Ocultar el spinner
                alert('Error al procesar la solicitud');
            } finally {
                isSubmitting = false;
            }
        }

        submitBtn.addEventListener('click', handleSubmit);
        form.addEventListener('submit', handleSubmit);
        form.reset();
    });

    window.history.replaceState(null, null, window.location.href);
    </script>
</body>
</html>
