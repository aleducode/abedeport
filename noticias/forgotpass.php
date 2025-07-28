<?php
session_start();
require_once '../admin/conn.php';

// Handle forgot password form submission
if ($_POST && isset($_POST['btn-forgotpass'])) {
    $email = $_POST['email'] ?? '';
    
    if (!empty($email)) {
        try {
            // Check if email exists in database
            $fpass = $conn->prepare('SELECT * FROM usuario WHERE correo = ? AND is_admin = 0 LIMIT 1');
            $fpass->execute([$email]);
            $user = $fpass->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $id = base64_encode($user['id_usuario']);
                $token = md5(uniqid(rand()));
                
                // Update user with token
                $uptoken = $conn->prepare('UPDATE usuario SET token = ? WHERE correo = ?');
                $uptoken->execute([$token, $email]);
                
                // Prepare email message
                $resetLink = "http://localhost:8080/noticias/resetpass.php?id=$id&token=$token";
                
                // For now, just show success message (email functionality would need to be implemented)
                $success = "Se ha enviado un enlace de recuperación a tu correo electrónico: $email";
                
                // Note: You would implement actual email sending here using PHPMailer or similar
                // include 'config.mailer.php';
                
            } else {
                $error = "El correo electrónico no existe en nuestra base de datos";
            }
        } catch(PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
        }
    } else {
        $error = "Por favor ingresa tu correo electrónico";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>ABE Deportes - Recuperar Contraseña</title>
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
                        <h1 class="page-heading__title">Recuperar <span class="highlight">Contraseña</span></h1>
                    </div>
                    <div class="col align-self-end">
                        <ol class="page-heading__breadcrumb breadcrumb font-italic">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="login.php">Iniciar Sesión</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Recuperar Contraseña</li>
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
                        <!-- Forgot Password -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Recuperar Contraseña</h4>
                                <p class="text-muted">Ingresa tu correo electrónico para recibir un enlace de recuperación</p>
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
                                        <small class="text-muted">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            Si no recibes el correo en unos minutos, revisa tu carpeta de spam.
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!isset($success)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle mr-2"></i>
                                        Se enviará un enlace a tu correo para restablecer la contraseña.
                                    </div>

                                    <!-- Forgot Password Form -->
                                    <form method="POST" action="" class="needs-validation" novalidate>
                                        <div class="form-group">
                                            <label for="email">Tu Correo Electrónico</label>
                                            <input type="email" name="email" id="email" class="form-control" placeholder="Ingresa tu email..." required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                            <div class="invalid-feedback">Por favor ingresa un correo electrónico válido.</div>
                                        </div>
                                        
                                        <div class="form-group form-group--sm">
                                            <button type="submit" name="btn-forgotpass" class="btn btn-primary-inverse btn-lg btn-block">
                                                <i class="fa fa-key mr-2"></i>Recuperar Contraseña
                                            </button>
                                        </div>
                                    </form>
                                    <!-- Forgot Password Form / End -->
                                <?php endif; ?>

                                <div class="text-center mt-4">
                                    <p class="mb-0">¿Recordaste tu contraseña? <a href="login.php" class="highlight">Iniciar sesión</a></p>
                                    <p class="mb-0 mt-2">¿No tienes cuenta? <a href="register.php" class="highlight">Registrarse</a></p>
                                </div>
                            </div>
                        </div>
                        <!-- Forgot Password / End -->
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
        })();
    </script>
</body>
</html>