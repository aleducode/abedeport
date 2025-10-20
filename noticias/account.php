<?php
session_start();
require_once '../admin/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data from database
try {
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // User not found, destroy session and redirect
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch(PDOException $e) {
    $error = "Error al cargar los datos del usuario";
}

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $documento = $_POST['documento'] ?? '';
    
    if (!empty($nombre) && !empty($apellido) && !empty($documento)) {
        try {
            $updateStmt = $conn->prepare("UPDATE usuario SET nombre = ?, apellido = ?, documento = ? WHERE id_usuario = ?");
            if ($updateStmt->execute([$nombre, $apellido, $documento, $_SESSION['user_id']])) {
                $_SESSION['user_name'] = $nombre . ' ' . $apellido;
                $success = "Perfil actualizado correctamente";
                // Refresh user data
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error al actualizar el perfil";
            }
        } catch(PDOException $e) {
            $error = "Error de base de datos";
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}

// Get user's sports preferences (stored in red_social field)
$userSports = !empty($user['red_social']) ? explode(',', $user['red_social']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>ABE Deportes - Mi Cuenta</title>
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
                            <li class="nav-account__item">
                                <a href="account.php">
                                    <i class="fas fa-user"></i> 
                                    Hola, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>
                                </a>
                            </li>
                            <li class="nav-account__item nav-account__item--logout">
                                <a href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> 
                                    Cerrar Sesión
                                </a>
                            </li>
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
                        <h1 class="page-heading__title">Mi <span class="highlight">Cuenta</span></h1>
                    </div>
                    <div class="col align-self-end">
                        <ol class="page-heading__breadcrumb breadcrumb font-italic">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mi Cuenta</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Filter -->
        <nav class="content-filter content-filter--boxed content-filter--highlight-side content-filter--label-left">
            <div class="container">
                <div class="content-filter__inner">
                    <a href="#" class="content-filter__toggle"></a>
                    <ul class="content-filter__list">
                        <li class="content-filter__item content-filter__item--active">
                            <a href="#" class="content-filter__link">
                                <small>Mi Cuenta</small>Información Personal
                            </a>
                        </li>
                        <li class="content-filter__item">
                            <a href="#" class="content-filter__link">
                                <small>Mi Cuenta</small>Preferencias Deportivas
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="site-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Account Information -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Información Personal</h4>
                            </div>
                            <div class="card__content">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($success)): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                <?php endif; ?>

                                <!-- Profile Form -->
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nombre">Nombres</label>
                                                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="apellido">Apellidos</label>
                                                <input type="text" name="apellido" id="apellido" class="form-control" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="correo">Correo Electrónico</label>
                                        <input type="email" id="correo" class="form-control" value="<?php echo htmlspecialchars($user['correo']); ?>" disabled>
                                        <small class="form-text text-muted">No puedes cambiar tu correo electrónico</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="documento">Documento de Identidad</label>
                                        <input type="text" name="documento" id="documento" class="form-control" value="<?php echo htmlspecialchars($user['documento']); ?>" required>
                                    </div>

                                    <div class="form-group form-group--sm">
                                        <button type="submit" name="update_profile" class="btn btn-primary-inverse btn-lg">
                                            <i class="fas fa-save mr-2"></i>Actualizar Perfil
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Sports Preferences -->
                        <div class="card">
                            <div class="card__header">
                                <h4>Deportes de Interés</h4>
                            </div>
                            <div class="card__content">
                                <?php if (!empty($userSports)): ?>
                                    <div class="mb-3">
                                        <?php foreach ($userSports as $sport): ?>
                                            <?php
                                            $sportNames = [
                                                'futbol' => ['name' => 'Fútbol', 'icon' => 'fa-futbol'],
                                                'futsal' => ['name' => 'Futsal', 'icon' => 'fa-running'],
                                                'baloncesto' => ['name' => 'Baloncesto', 'icon' => 'fa-basketball-ball'],
                                                'voleibol' => ['name' => 'Voleibol', 'icon' => 'fa-volleyball-ball']
                                            ];
                                            $sportInfo = $sportNames[trim($sport)] ?? ['name' => $sport, 'icon' => 'fa-circle'];
                                            ?>
                                            <span class="badge badge-primary mr-2 mb-2 p-2">
                                                <i class="fas <?php echo $sportInfo['icon']; ?> mr-1"></i>
                                                <?php echo htmlspecialchars($sportInfo['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No has seleccionado deportes de interés.</p>
                                <?php endif; ?>
                                
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Basado en tus preferencias, personalizamos las noticias que ves.
                                </small>
                            </div>
                        </div>

                        <!-- Account Stats -->
                        <div class="card mt-4">
                            <div class="card__header">
                                <h4>Estadísticas</h4>
                            </div>
                            <div class="card__content">
                                <div class="row text-center">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <h5 class="text-primary mb-1"><?php echo date('d/m/Y', strtotime($user['estado'] ?? 'now')); ?></h5>
                                            <small class="text-muted">Miembro desde</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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