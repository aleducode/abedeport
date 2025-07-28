<?php
session_start();
require_once '../admin/conn.php';

// Handle login form submission
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        try {
            // Query non-admin users only
            $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ? AND is_admin = 0");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
                $_SESSION['user_email'] = $user['correo'];
                $_SESSION['user_is_admin'] = $user['is_admin'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = "Credenciales incorrectas";
            }
        } catch(PDOException $e) {
            $error = "Error de conexión";
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>ABE Deportes - Iniciar Sesión</title>
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
                        <h1 class="page-heading__title">Iniciar <span class="highlight">Sesión</span></h1>
                    </div>
                    <div class="col align-self-end">
                        <ol class="page-heading__breadcrumb breadcrumb font-italic">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Iniciar Sesión</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="site-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <!-- Login -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Accede a tu cuenta</h4>
                            </div>
                            <div class="card__content">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>

                                <!-- Login Form -->
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="email">Tu Email</label>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Ingresa tu email..." required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Tu Contraseña</label>
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Ingresa tu contraseña..." required>
                                    </div>
                                    <div class="form-group form-group--password-forgot">
                                        <label class="checkbox checkbox-inline">
                                            <input type="checkbox" name="remember" value="1"> Recordarme
                                            <span class="checkbox-indicator"></span>
                                        </label>
                                        <span class="password-reminder">¿Olvidaste tu contraseña? <a href="forgotpass.php">Haz clic aquí</a></span>
                                    </div>
                                    <div class="form-group form-group--sm">
                                        <button type="submit" class="btn btn-primary-inverse btn-lg btn-block">Iniciar Sesión</button>
                                    </div>
                                </form>
                                <!-- Login Form / End -->
                            </div>
                        </div>
                        <!-- Login / End -->
                    </div>

                    <div class="col-lg-6">
                        <!-- Register -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Crear cuenta nueva</h4>
                            </div>
                            <div class="card__content">
                                <p class="text-muted mb-4">¿No tienes cuenta? Regístrate para acceder a todas las funciones del portal deportivo.</p>
                                
                                <div class="form-group form-group--sm">
                                    <a href="register.php" class="btn btn-default btn-lg btn-block">Registrarse</a>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Al registrarte tendrás acceso a:
                                        <ul class="list-unstyled mt-2 text-left">
                                            <li><i class="fa fa-check text-success mr-2"></i> Comentar en las noticias</li>
                                            <li><i class="fa fa-check text-success mr-2"></i> Participar en competencias</li>
                                            <li><i class="fa fa-check text-success mr-2"></i> Recibir notificaciones</li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- Register / End -->
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
</body>
</html>