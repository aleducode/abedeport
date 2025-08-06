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
    <title>AEDEPORT - Panel de Administración</title>
    <link rel="shortcut icon" href="../assets/img/LOGO.png" type="image/x-icon">
    <meta name="author" content="Matías Felipe García Botero">
    <meta name="description" content="Panel de administración para AEDEPORT">
    <meta name="Keywords" content="DEPORTE, recreacion, Actividad, Blog, Administración">
    <meta name="theme-color" content="#008000">
    <meta name="MobileOptimized" content="width">
    <meta name="handhledFriendly" content="true">
    <meta name="Mobile-web-app-capable" content="true">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-traslucent">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: rgb(173, 75, 19);
            --bs-primary: rgb(173, 75, 19);
            --bs-primary-rgb: 173, 75, 19;
        }
        
        body {
            padding-top: 4.5rem;
            min-height: 100vh;
            background-color: #212529;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .navbar-brand img {
            width: 40px;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover img {
            transform: scale(1.1);
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
        
        .nav-link {
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            border-radius: 4px;
            margin: 0 0.2rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(173, 75, 19, 0.2);
            color: var(--primary-color);
        }
        
        .content-container {
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            background-color: #2c3034;
            margin-top: 1.5rem;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .user-welcome {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background-color: rgba(173, 75, 19, 0.1);
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .admin-nav {
            background: linear-gradient(135deg, var(--primary-color), #8B4513);
            border-radius: 0.5rem;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .admin-nav .nav-link {
            color: white;
            border-radius: 0.25rem;
            margin: 0.1rem;
        }
        
        .admin-nav .nav-link:hover, .admin-nav .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
    </style>
</head>

<body class="d-flex flex-column">
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="home">
                    <img src="../assets/img/sports.png" alt="AEDEPORT Logo" class="me-2">
                    <span class="fw-bold text-primary">AEDEPORT</span>
                    <span class="badge bg-warning text-dark ms-2">Admin</span>
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'admin_dashboard') ? 'active' : ''; ?>" 
                               href="?page=admin_dashboard">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'create_post') ? 'active' : ''; ?>" 
                               href="?page=create_post">
                                <i class="bi bi-plus-circle me-1"></i>Nuevo Post
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'manage_posts') ? 'active' : ''; ?>" 
                               href="?page=manage_posts">
                                <i class="bi bi-list-ul me-1"></i>Gestionar Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'pubs') ? 'active' : ''; ?>" 
                               href="?page=pubs">
                                <i class="bi bi-newspaper me-1"></i>Publicaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'team') ? 'active' : ''; ?>" 
                               href="?page=team">
                                <i class="bi bi-people-fill me-1"></i>Integrantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'blog_viewer') ? 'active' : ''; ?>" 
                               href="?page=blog_viewer" target="_blank">
                                <i class="bi bi-newspaper me-1"></i>Ver Blog
                            </a>
                        </li>
                    </ul>
                    
                    <div class="d-flex align-items-center">
                        <span class="text-white me-3">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($data['nombre']); ?>
                        </span>
                        <a href="./logout" class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container flex-grow-1 py-4">
        <div class="content-container">
            <?php
            //Controlador de modulos o subpáginas
            $page = isset($_GET['page']) ? strtolower($_GET['page']) : 'admin_dashboard';
            
            // List of admin pages
            $admin_pages = [
                'admin_dashboard' => 'admin_dashboard.php',
                'create_post' => 'create_post.php',
                'edit_post' => 'edit_post.php',
                'view_post' => 'view_post.php',
                'manage_posts' => 'manage_posts.php',
                'preview_post' => 'preview_post.php',
                'blog_viewer' => 'blog_viewer.php'
            ];
            
            if (array_key_exists($page, $admin_pages)) {
                require_once $admin_pages[$page];
            } elseif ($page == 'home') {
                echo '<div class="user-welcome"><i class="bi bi-hand-thumbs-up-fill me-2"></i>Bienvenido, ' . htmlspecialchars($data['nombre']) . ' ' . htmlspecialchars($data['apellido']) . '</div>';
                require_once 'init.php';
            } else {
                // Check if the page exists
                $page_file = './'. $page . '.php';
                if (file_exists($page_file)) {
                    require_once $page_file;
                } else {
                    echo '<div class="alert alert-warning">Página no encontrada.</div>';
                    require_once 'admin_dashboard.php';
                }
            }
            ?>
        </div>
    </main>
    
    <footer class="bg-dark text-center text-white-50 py-3 mt-auto">
        <div class="container">
            <small>&copy; <?php echo date('Y'); ?> AEDEPORT - Panel de Administración - Todos los derechos reservados</small>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    }
} else {
    // Si no hay sesión iniciada, redirigir a la página de inicio de sesión
    header("location: ./");
}
?>
</body>
</html>
