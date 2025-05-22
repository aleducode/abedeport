<?php
require_once 'conn.php';

if (isset($_POST['btn-reg'])) {
    $insert = $conn->prepare('INSERT INTO usuario(nombre, apellido, documento, correo, password) VALUES(?,?,?,?,?)');
    $insert->bindParam(1, $_POST['nombre']);
    $insert->bindParam(2, $_POST['apellido']);
    $insert->bindParam(3, $_POST['documento']);
    $insert->bindParam(4, $_POST['correo']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $insert->bindParam(5, $password);

    /* Data Validation */
    $search = $conn->prepare('SELECT * FROM usuario WHERE correo = ?');
    $search->bindParam(1, $_POST['correo']);
    $search->execute();
    $result = $search->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $msg = array("Correo ya existente", "danger");
    }
    /* Data Validation */ elseif ($insert->execute()) {
        $msg = array("El usuario fue creado ", "success");
    } else {
        $msg = array("El usuario fue no creado", "danger");
    }
}
?>
<!DOCTYPE html>
<html lang="CO">


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
    <link rel="stylesheet" href="../css/styles.css">
</head>
<style>
    .navbar {
        box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.3);
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
</style>

<body>
    <main class="container">
        <div class="card  text-black navbar " style="color: rgb(173, 75, 19); border:1px solid black;">
            <div class="card-body">
                <!--Alerts-->
                <?php if (isset($msg)) { ?>

                    <div class="alert alert-<?php echo $msg[1]; ?> alert-dismissible">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <strong>Alerta!</strong> <?php echo $msg[0]; ?>.
                    </div>

                <?php } ?>
                <!--Alerts-->
                <div class="text-center">
                    <h1 class="display-6 animacion1">Registro de Usuario</h1>
                </div>
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="../assets/img/foto.3.png" class="rounded" alt="logo" width="200px">
                        </td>
                    </tr>
                </table>
                <form action="" method="post" enctype="application/x-www-form-urlencoded">
                    <div class="mb-3 mt-3 ">
                        <label for="nombre" class="form-label ">Nombres</label>
                        <input type="text" class="form-control efecto" id="nombre" placeholder="Porfavor ingrese sus nombres"
                            name="nombre" required style=" border:1px solid black;">
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="apellido" class="form-label">Apellidos</label>
                        <input type="text" class="form-control efecto" id="apellido" placeholder="Porfavor ingrese sus apellidos"
                            name="apellido" required style=" border:1px solid black;">
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="correo" class="form-label">Correo Electronico</label>
                        <input type="text" class="form-control efecto" id="fname" placeholder="Porfavor ingrese su correo"
                            name="correo" required style=" border:1px solid black;">
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="documento" class="form-label">Docuemnto de identidad</label>
                        <input type="text" class="form-control efecto" id="fname" placeholder="Porfavor ingrese su docuemnto de identidad"
                            name="documento" required style=" border:1px solid black;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label ">Contraseña</label>
                        <div class="input-group efecto">
                            <span class="input-group-text " style="border:1px solid "><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control " id="password" placeholder="Ingreasa tu Contraseña" name="password" required style="border:1px solid ">
                            <span class="input-group-text" onclick="pass_show_hide();" style="border:1px solid ">
                                <i class="bi bi-eye-fill d-none" id="showeye"></i>
                                <i class="bi bi-eye-slash" id="hideeye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <button type="submit" class="btn w-100 text-white fw-bold mt-3 efecto-black" name="btn-reg"
                            style="background-color: rgb(173, 75, 19); border-radius: 8px">
                            <i class="bi bi-arrow-right-square"></i> <span style="display: inline-block; width: 2px"></span>Registrarse
                        </button>
                        <div class=" col-8; form-check mb-3 clearfix ">
                            <label class="form-check-label float-end">
                                <a href="./">
                                    <p class="text-white">
                                        <p class="color">Iniciarse Sesión</p>
                                    </p>
                                </a>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    <span style="display: inline-block; height: 100px;"></span>
    </main>

    <!--Complements JS-->
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <!--Script visualización password-->
    <script src="../assets/js/password.viewer.js"></script>
</body>

</html>