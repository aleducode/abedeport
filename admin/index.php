<?php
session_start();
require_once 'conn.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    // Redirect to admin login or show login form
    header('Location: login.php');
    exit();
}

// If logged in, show admin dashboard
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABEDEPORT - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .admin-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .card { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); border: none; }
        .nav-link:hover { background-color: rgba(255,255,255,0.1); border-radius: 0.25rem; }
    </style>
</head>
<body>
    <div class="admin-header py-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tachometer-alt"></i> ABEDEPORT Admin</h2>
                <div>
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrador'); ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-bars"></i> Menú</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="admin_dashboard.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="manage_posts.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-newspaper"></i> Gestionar Posts
                        </a>
                        <a href="create_post.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus"></i> Crear Post
                        </a>
                        <a href="tournaments.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-trophy"></i> Torneos
                        </a>
                        <a href="leaderboard.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-medal"></i> Clasificaciones
                        </a>
                        <a href="reg_users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users"></i> Usuarios
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-home"></i> Panel Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h4><i class="fas fa-newspaper"></i></h4>
                                        <h6>Gestionar Posts</h6>
                                        <p>Administra artículos y noticias</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h4><i class="fas fa-trophy"></i></h4>
                                        <h6>Torneos</h6>
                                        <p>Administra competencias</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h4><i class="fas fa-users"></i></h4>
                                        <h6>Usuarios</h6>
                                        <p>Gestiona usuarios registrados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Bienvenido al Panel de Administración</h6>
                                    <p>Desde aquí puedes gestionar todos los aspectos de ABEDEPORT. Utiliza el menú lateral para navegar entre las diferentes secciones.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-key.js" crossorigin="anonymous"></script>
</body>
</html> 