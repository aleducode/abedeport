<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABEDEPORT - Bienvenido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <h1 class="card-title mb-4">Bienvenido a ABEDEPORT</h1>
                        <p class="card-text mb-4">Portal de deportes y noticias</p>
                        <div class="d-grid gap-3">
                            <a href="/noticias/" class="btn btn-primary btn-lg">
                                <i class="bi bi-newspaper me-2"></i>Ver Noticias
                            </a>
                            <a href="/admin/" class="btn btn-outline-secondary">
                                <i class="bi bi-gear me-2"></i>Panel de Administración
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>