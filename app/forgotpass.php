<?php
require 'conn.php';
session_start();

if (isset($_POST['btn-formulpass'])) {
    $email = $_POST['correo'];

    $fpass = $conn->prepare('SELECT * FROM usuario WHERE correo = ? LIMIT 1');
    $fpass->bindParam(1, $email);
    $fpass->execute();
    $row = $fpass->fetch(PDO::FETCH_ASSOC);

    if ($fpass->rowCount() == 1) {
        $id = base64_encode($row['id_usuario']);
        $token = md5(uniqid(rand()));

        $uptoken = $conn->prepare('UPDATE usuario SET token = ? WHERE correo = ?');
        $uptoken->bindParam(1, $token);
        $uptoken->bindParam(2, $email);
        $uptoken->execute();

        //preparar el mensaje y el asunto del correo que voy a enviar
        $subject = '=?UTF-8?B?' . base64_encode("Restablecer Contraseña") . "=?=";

        $message = "Restablecer Contraseña\n\n";
        $message .= "Para restablecer su contraseña, haga clic en el siguiente enlace:\n\n";
        $message .= "http://localhost/abedeport/app/resetpass?id=$id&token=$token";

        include 'config.mailer.php';
        
        $msg = array("Se ha enviado un enlace de recuperación a su correo electrónico", "success");
    } else {
        $msg = array("El correo electrónico no existe en nuestra base de datos", "danger");
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
        
        .alert-info-custom {
            background-color: rgba(173, 75, 19, 0.1);
            border-color: rgba(173, 75, 19, 0.2);
            color: var(--primary-color);
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
                    <h1 class="card-title h3 mt-3">Recuperar Contraseña</h1>
                    <p class="text-muted small">Ingresa tu correo electrónico para recibir un enlace de recuperación</p>
                </div>
                
                <div class="alert alert-info-custom mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Se enviará un enlace a tu correo para restablecer la contraseña.
                </div>

                <form action="" method="post" class="needs-validation" novalidate>
                    <div class="form-floating mb-4">
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
                        <label for="correo"><i class="bi bi-envelope-fill me-2"></i>Correo Electrónico</label>
                        <div class="invalid-feedback">Por favor ingresa un correo electrónico válido.</div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg" name="btn-formulpass">
                            <i class="bi bi-key-fill me-2"></i>Recuperar Contraseña
                        </button>
                    </div>

                    <div class="text-center links-container">
                        <p class="mb-0">¿Recordaste tu contraseña? <a href="./" class="fw-semibold">Iniciar sesión</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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