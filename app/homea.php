<?php 
include "conn.php";
session_start();

// verifica la sesión que se está iniciando
if (isset($_SESSION['usuario'])) {
    $search = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
    $search->bindParam(1, $_SESSION['usuario']);
    $search->execute();
    $data = $search->fetch(PDO::FETCH_ASSOC);

    if (is_array($data)) {

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
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="../assets/img/logo.png" alt="Avatar Logo" style="width: 40px" class="" />
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsibleNavbar" aria-label="Boton de menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="home">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=pubs">Publicaciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#team">Integrantes</a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <a href="./logout" class="btn btn-primary">Salir</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>
    <?php
    //Controlador de modulos o subpáginas
    $page = isset($_GET['page']) ? strtolower($_GET['page']) : 'home';
    require_once './'. $page . '.php';

    if ($page == 'home') {
        require_once 'init.php';
    }
    ?>
    </main>
    <?php
    }
}else{
    // Si no hay sesión iniciada, redirigir a la página de inicio de sesión
    header("location: ./");
}
?>
</body>
</html>
