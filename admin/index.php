<?php
session_start();

/*
* Validación de Usuarios 
* Seguridad de la aplicación en el home

*/
require_once 'conn.php';
/*
* Validar si el usuario ya se encuentra logueado
* Si el usuario ya se encuentra logueado, redirigirlo a la página de home
 */
if (isset($_SESSION['usuario'])) {
    header('Location: home');
    exit();
}

// Check for access denied error
if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
    $msg = array("Acceso denegado. Solo los administradores pueden acceder a esta área.", "danger");
}

if (isset($_POST['btnlogin'])) {
    $login = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
    $login->bindParam(1, $_POST['correo']);
    $login->execute();
    $result = $login->fetch(PDO::FETCH_ASSOC);

    if (is_array($result)) {
        if (password_verify($_POST['password'], $result['password'])) {
            // Check if user is admin
            if (!isset($result['is_admin']) || $result['is_admin'] != 1) {
                $msg = array("Acceso denegado. Solo los administradores pueden acceder a esta área.", "danger");
            } else {
                $_SESSION['usuario'] = $result['correo'];
                $_SESSION['id_usuario'] = $result['id_usuario'];
                header('Location: home');
                exit();
            }
        } else {
            $msg = array("Contraseña incorrecta", "warning");
        }
    } else {
        $msg = array("El correo no existe", "danger");
    }
}
?>

<!DOCTYPE html>
<html lang="es-CO" data-bs-theme="dark" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AEDEPORT</title>
    <link rel="shortcut icon" href="../assets/img/LOGO.png" type="image/x-icon">
    <meta name="author" content="Matías Felipe García Botero">
    <meta name="description" content="aplicativo web ">
    <meta name="Keywords" content="DEPORTE, recreacion, Actividad ">
    <meta name="theme-color" content="#008000">
    <meta name="MobileOptimized" content="width">
    <meta name="handhledFriendly" content="true">
    <meta name="Mobile-web-app-capable" content="true">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-traslucent">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        :root {
            --primary-color: rgb(173, 75, 19);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: rgba(173, 75, 19, 0.85);
            border-color: var(--primary-color);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(173, 75, 19, 0.25);
        }
        
        .login-container {
            max-width: 450px;
            margin: 2rem auto;
        }
        
        .card {
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .card-title:hover {
            transform: scale(1.05);
        }
        
        .login-logo {
            max-width: 150px;
            margin: 1rem auto;
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .form-floating>.form-control:focus~label {
            color: var(--primary-color);
        }
        
        .links-container a {
            color: var(--primary-color);
            text-decoration: none;
            transition: opacity 0.3s ease;
        }
        
        .links-container a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }
    </style>
</head>

<body class="d-flex align-items-center py-4 bg-dark">
    <main class="login-container container">
        <?php if (isset($msg)) { ?>
            <div class="alert alert-<?php echo $msg[1]; ?> alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $msg[0]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <div class="card border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <img src="../assets/img/sports.png" class="login-logo img-fluid rounded" alt="AEDEPORT Logo">
                    <h1 class="card-title h3 mt-3">Iniciar Sesión</h1>
                    <p class="text-muted small">Ingresa tus credenciales para acceder</p>
                </div>

                <form action="" method="post" class="needs-validation" novalidate>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
                        <label for="correo"><i class="bi bi-envelope-fill me-2"></i>Correo Electrónico</label>
                        <div class="invalid-feedback">Por favor ingresa un correo electrónico válido.</div>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                        <label for="password"><i class="bi bi-lock-fill me-2"></i>Contraseña</label>
                        <div class="invalid-feedback">Por favor ingresa tu contraseña.</div>
                        <div class="form-text text-end">
                            <a href="forgotpass" class="small">¿Olvidaste la contraseña?</a>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg" name="btnlogin">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </button>
                    </div>

                    <div class="text-center links-container">
                        <p class="mb-0 text-muted small">Solo administradores autorizados</p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!--Complements JS-->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Password toggle
            const passwordInput = document.getElementById('password');
            const togglePassword = document.createElement('button');
            togglePassword.type = 'button';
            togglePassword.className = 'btn position-absolute end-0 top-50 translate-middle-y';
            togglePassword.innerHTML = '<i class="bi bi-eye-slash"></i>';
            togglePassword.style.zIndex = '5';
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            });
            
            const passwordParent = passwordInput.parentElement;
            passwordParent.classList.add('position-relative');
            passwordParent.appendChild(togglePassword);
        })();
    </script>
</body>

</html>