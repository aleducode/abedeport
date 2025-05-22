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
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<style>
    .navbar {
        box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.3);
    }

    .color {
        color: rgb(173, 75, 19);
    }

    .efecto {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transition: transform 0.5s ease, box-shadow 0.5s ease;
    }

    .efecto:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgb(173, 75, 19);
    }

    .efecto-black {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transition: transform 0.5s ease, box-shadow 0.5s ease;
    }

    .efecto-black:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgb(0, 0, 0);
    }

    .animacion1 {
        color: rgb(173, 75, 19);
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease, text-shadow 0.3s ease;
    }

    .animacion1:hover {
        transform: scale(1.05);
        text-shadow: 4px 4px 10px rgba(0, 0, 0, 0.7);
    }
</style>

<body>
    <main class="container">

        <div class="card  text-black navbar " style="color: rgb(173, 75, 19); border:1px solid black;">
            <div class="card-body">
                <!--Alerts -->
                <?php if (isset($msg)) { ?>
                    <div class="alert alert-<?php echo $msg[1]; ?> alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <strong>Aviso!</strong> <?php echo $msg[0]; ?>
                    </div>
                <?php } ?>
                <!--Alerts -->

                <div class="alert alert-warning" role="alert">
                    Se enviara un link a su correo para restablecer la contraseña.
                </div>
                <div class="text-center">
                    <h1 class="display-6 color animacion1">Recuperar Contraseña</h1>
                </div>
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="../assets/img/foto.3.png" class="rounded" alt="logo" width="200px">
                        </td>
                    </tr>
                </table>
                <form action="" method="post" enctype="application/x-www-form-urlencoded">
                    <div class="mb-3 mt-3">
                        <label for="correo" class="form-label">Correo Electronico</label>
                        <input type="text" class="form-control efecto" id="fname" placeholder="Porfavor ingrese su correo"
                            name="correo" required style=" border:1px solid black;">
                    </div>

                    <div class="row">
                        <button type="submit" class="btn w-100 text-white fw-bold mt-3 efecto-black" name="btn-formulpass"
                            style="background-color: rgb(173, 75, 19); border-radius: 8px">
                            <i class="bi bi-arrow-right-square"></i> <span style="display: inline-block; width: 2px"></span>Recuperar
                        </button>

                        <div class="row">
                            <div class="col-sm-">
                                <a href="./">
                                    <p class="color">Iniciar Sesión</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <!--Complements JS-->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>