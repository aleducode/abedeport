<?php
session_start();
require_once '../admin/conn.php';

// Test database connection
if (!$conn) {
    die("No database connection available");
}

// Handle registration form submission
if ($_POST) {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $deportes = $_POST['deportes'] ?? [];
    
    if (!empty($nombre) && !empty($apellido) && !empty($documento) && !empty($email) && !empty($password) && !empty($deportes)) {
        try {
            // Check if email already exists
            $search = $conn->prepare('SELECT * FROM usuario WHERE correo = ?');
            $search->execute([$email]);
            $result = $search->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $error = "El email ya está registrado";
            } else {
                // Insert new user with non-admin role
                // Convert multiple sports to comma-separated string and store in red_social field temporarily
                $deportesString = implode(',', $deportes);
                $insert = $conn->prepare('INSERT INTO usuario(nombre, apellido, documento, correo, password, red_social, is_admin, estado) VALUES(?,?,?,?,?,?,?,?)');
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $isAdmin = 0; // Set as regular user, not admin
                $estado = 1; // Active user
                
                // Debug: show the values being inserted
                error_log("Inserting: " . print_r([$nombre, $apellido, $documento, $email, $hashedPassword, $deportesString, $isAdmin, $estado], true));
                
                if ($insert->execute([$nombre, $apellido, $documento, $email, $hashedPassword, $deportesString, $isAdmin, $estado])) {
                    $success = "Usuario creado exitosamente. Ahora puedes iniciar sesión.";
                } else {
                    $error = "Error al crear el usuario";
                }
            }
        } catch(PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
        }
    } else {
        $error = "Por favor complete todos los campos obligatorios";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>ABE Deportes - Registro</title>
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
                        <h1 class="page-heading__title">Crear <span class="highlight">Cuenta</span></h1>
                    </div>
                    <div class="col align-self-end">
                        <ol class="page-heading__breadcrumb breadcrumb font-italic">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="login.php">Iniciar Sesión</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Registro</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="site-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Register -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Registro de Usuario</h4>
                                <p class="text-muted">Completa el formulario para crear tu cuenta</p>
                            </div>
                            <div class="card__content">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($success)): ?>
                                    <div class="alert alert-success">
                                        <?php echo htmlspecialchars($success); ?>
                                        <br><a href="login.php" class="alert-link">Ir a iniciar sesión</a>
                                    </div>
                                <?php endif; ?>

                                <!-- Register Form -->
                                <form method="POST" action="" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nombre">Nombres</label>
                                                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ingresa tus nombres..." required value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                                                <div class="invalid-feedback">Por favor ingresa tus nombres.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="apellido">Apellidos</label>
                                                <input type="text" name="apellido" id="apellido" class="form-control" placeholder="Ingresa tus apellidos..." required value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>">
                                                <div class="invalid-feedback">Por favor ingresa tus apellidos.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Correo Electrónico</label>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="Ingresa tu email..." required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                        <div class="invalid-feedback">Por favor ingresa un correo electrónico válido.</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="documento">Documento de Identidad</label>
                                        <input type="text" name="documento" id="documento" class="form-control" placeholder="Ingresa tu documento..." required value="<?php echo htmlspecialchars($_POST['documento'] ?? ''); ?>">
                                        <div class="invalid-feedback">Por favor ingresa tu documento de identidad.</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Contraseña</label>
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Ingresa tu contraseña..." required>
                                        <div class="invalid-feedback">Por favor ingresa una contraseña.</div>
                                    </div>

                                    <div class="form-group">
                                        <label>Deportes de Interés</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="checkbox checkbox-inline">
                                                    <input type="checkbox" name="deportes[]" value="futbol" <?php echo in_array('futbol', $_POST['deportes'] ?? []) ? 'checked' : ''; ?>>
                                                    <span class="checkbox-indicator"></span>
                                                    <i class="fa fa-futbol mr-2"></i>Fútbol
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="checkbox checkbox-inline">
                                                    <input type="checkbox" name="deportes[]" value="futsal" <?php echo in_array('futsal', $_POST['deportes'] ?? []) ? 'checked' : ''; ?>>
                                                    <span class="checkbox-indicator"></span>
                                                    <i class="fa fa-running mr-2"></i>Futsal
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="checkbox checkbox-inline">
                                                    <input type="checkbox" name="deportes[]" value="baloncesto" <?php echo in_array('baloncesto', $_POST['deportes'] ?? []) ? 'checked' : ''; ?>>
                                                    <span class="checkbox-indicator"></span>
                                                    <i class="fa fa-basketball-ball mr-2"></i>Baloncesto
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="checkbox checkbox-inline">
                                                    <input type="checkbox" name="deportes[]" value="voleibol" <?php echo in_array('voleibol', $_POST['deportes'] ?? []) ? 'checked' : ''; ?>>
                                                    <span class="checkbox-indicator"></span>
                                                    <i class="fa fa-volleyball-ball mr-2"></i>Voleibol
                                                </label>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback">Por favor selecciona al menos un deporte de interés.</div>
                                        <small class="form-text text-muted">Selecciona uno o más deportes para personalizar las noticias</small>
                                    </div>

                                    <div class="form-group form-group--sm">
                                        <button type="submit" class="btn btn-primary-inverse btn-lg btn-block">
                                            <i class="fa fa-user-plus mr-2"></i>Crear Cuenta
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <p class="mb-0">¿Ya tienes una cuenta? <a href="login.php" class="highlight">Iniciar sesión</a></p>
                                    </div>
                                </form>
                                <!-- Register Form / End -->
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
    
    <script>
        // Form validation
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    // Check if at least one sport is selected
                    const deporteCheckboxes = form.querySelectorAll('input[name="deportes[]"]');
                    const isDeporteSelected = Array.from(deporteCheckboxes).some(checkbox => checkbox.checked);
                    
                    // Set custom validity for sport selection
                    deporteCheckboxes.forEach(checkbox => {
                        checkbox.setCustomValidity(isDeporteSelected ? '' : 'Debes seleccionar al menos un deporte');
                    });
                    
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