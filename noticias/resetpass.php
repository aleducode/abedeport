<?php
session_start();
require_once '../admin/conn.php';

// Get parameters from URL
$id = $_GET['id'] ?? '';
$token = $_GET['token'] ?? '';

$validReset = false;
$user = null;

// Validate reset token
if (!empty($id) && !empty($token)) {
    try {
        $userId = base64_decode($id);
        $stmt = $conn->prepare('SELECT * FROM usuario WHERE id_usuario = ? AND token = ? AND is_admin = 0 LIMIT 1');
        $stmt->execute([$userId, $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $validReset = true;
        }
    } catch(PDOException $e) {
        $error = "Error de conexión";
    }
}

// Handle password reset form submission
if ($_POST && isset($_POST['btn-resetpass']) && $validReset) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (!empty($password) && !empty($confirmPassword)) {
        if ($password === $confirmPassword) {
            if (strlen($password) >= 6) {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare('UPDATE usuario SET password = ?, token = NULL WHERE id_usuario = ?');
                    
                    if ($updateStmt->execute([$hashedPassword, $user['id_usuario']])) {
                        $success = "Contraseña actualizada correctamente. Ahora puedes iniciar sesión.";
                        $validReset = false; // Prevent further resets
                    } else {
                        $error = "Error al actualizar la contraseña";
                    }
                } catch(PDOException $e) {
                    $error = "Error de base de datos";
                }
            } else {
                $error = "La contraseña debe tener al menos 6 caracteres";
            }
        } else {
            $error = "Las contraseñas no coinciden";
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>ABE Deportes - Restablecer Contraseña</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="ABE Deportes - Portal de noticias deportivas">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
    
    <!-- CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="assets/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style-esports.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
</head>
<body data-template="template-esports" class="page-loader-disable">

    <div class="site-wrapper clearfix">
        <div class="site-overlay"></div>

        <!-- Header Mobile -->
        <div class="header-mobile clearfix" id="header-mobile">
            <div class="header-mobile__logo">
                <a href="index.php"><img src="assets/images/esports/logo.png" alt="ABE Deportes" class="header-mobile__logo-img" style="width: 100px; height: 100px;"></a>
            </div>
            <div class="header-mobile__inner">
                <a id="header-mobile__toggle" class="burger-menu-icon"><span class="burger-menu-icon__line"></span></a>
            </div>
        </div>

        <!-- Header Desktop -->
        <header class="header header--layout-3">
            <div class="header__top-bar clearfix">
                <div class="container">
                    <div class="header__top-bar-inner">
                        <!-- Social Links -->
                        <ul class="social-links social-links--inline social-links--main-nav social-links--top-bar">
                            <li class="social-links__item">
                                <a href="#" class="social-links__link"><i class="fab fa-facebook"></i></a>
                            </li>
                            <li class="social-links__item">
                                <a href="#" class="social-links__link"><i class="fab fa-twitter"></i></a>
                            </li>
                            <li class="social-links__item">
                                <a href="#" class="social-links__link"><i class="fab fa-instagram"></i></a>
                            </li>
                        </ul>

                        <!-- Account Navigation -->
                        <ul class="nav-account">
                            <li class="nav-account__item"><a href="login.php">Iniciar Sesión</a></li>
                            <li class="nav-account__item"><a href="index.php">Volver al inicio</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="header__primary">
                <div class="container">
                    <div class="header__primary-inner">
                        <!-- Header Logo -->
                        <div class="header-logo">
                            <a href="index.php"><img src="assets/images/esports/logo.png" alt="ABE Deportes" class="header-logo__img" style="width: 100px; height: 100px;"></a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Heading -->
        <div class="page-heading page-heading--horizontal effect-duotone effect-duotone--primary">
            <div class="container">
                <div class="row">
                    <div class="col align-self-start">
                        <h1 class="page-heading__title">Restablecer <span class="highlight">Contraseña</span></h1>
                    </div>
                    <div class="col align-self-end">
                        <ol class="page-heading__breadcrumb breadcrumb font-italic">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="login.php">Iniciar Sesión</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Restablecer Contraseña</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="site-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <!-- Reset Password -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Restablecer Contraseña</h4>
                                <?php if ($validReset): ?>
                                    <p class="text-muted">Ingresa tu nueva contraseña para el usuario: <strong><?php echo htmlspecialchars($user['correo']); ?></strong></p>
                                <?php endif; ?>
                            </div>
                            <div class="card__content">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($success)): ?>
                                    <div class="alert alert-success">
                                        <strong><i class="fa fa-check-circle mr-2"></i>¡Éxito!</strong><br>
                                        <?php echo htmlspecialchars($success); ?>
                                        <br><br>
                                        <a href="login.php" class="btn btn-primary">Ir a iniciar sesión</a>
                                    </div>
                                <?php elseif (!$validReset): ?>
                                    <div class="alert alert-danger">
                                        <strong><i class="fa fa-exclamation-triangle mr-2"></i>Enlace inválido</strong><br>
                                        El enlace de restablecimiento es inválido o ha expirado.
                                        <br><br>
                                        <a href="forgotpass.php" class="btn btn-primary">Solicitar nuevo enlace</a>
                                    </div>
                                <?php else: ?>
                                    <!-- Reset Password Form -->
                                    <form method="POST" action="" class="needs-validation" novalidate>
                                        <div class="form-group">
                                            <label for="password">Nueva Contraseña</label>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Ingresa tu nueva contraseña..." required minlength="6">
                                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password">Confirmar Contraseña</label>
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirma tu nueva contraseña..." required minlength="6">
                                            <div class="invalid-feedback">Por favor confirma tu contraseña.</div>
                                        </div>
                                        
                                        <div class="form-group form-group--sm">
                                            <button type="submit" name="btn-resetpass" class="btn btn-primary-inverse btn-lg btn-block">
                                                <i class="fa fa-lock mr-2"></i>Actualizar Contraseña
                                            </button>
                                        </div>
                                    </form>
                                    <!-- Reset Password Form / End -->
                                <?php endif; ?>

                                <div class="text-center mt-4">
                                    <p class="mb-0"><a href="login.php" class="highlight">Volver a iniciar sesión</a></p>
                                </div>
                            </div>
                        </div>
                        <!-- Reset Password / End -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer id="footer" class="footer">
            <div class="footer__copyright">
                <div class="container">
                    <div class="footer__copyright-inner">
                        <div class="footer__copyright-content">
                            <p>&copy; 2024 ABE Deportes. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JS -->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/core.js"></script>
    <script src="assets/js/init.js"></script>
    
    <script>
        // Form validation with password matching
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    const password = form.querySelector('#password');
                    const confirmPassword = form.querySelector('#confirm_password');
                    
                    // Check if passwords match
                    if (password && confirmPassword) {
                        if (password.value !== confirmPassword.value) {
                            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                        } else {
                            confirmPassword.setCustomValidity('');
                        }
                    }
                    
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
                
                // Real-time password matching validation
                const password = form.querySelector('#password');
                const confirmPassword = form.querySelector('#confirm_password');
                
                if (password && confirmPassword) {
                    [password, confirmPassword].forEach(input => {
                        input.addEventListener('input', () => {
                            if (password.value !== confirmPassword.value) {
                                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                            } else {
                                confirmPassword.setCustomValidity('');
                            }
                        });
                    });
                }
            });
        })();
    </script>
</body>
</html>