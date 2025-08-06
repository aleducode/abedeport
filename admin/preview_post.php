<?php
// This page is for previewing posts before publishing
$titulo = $_POST['titulo'] ?? 'Título del Post';
$contenido = $_POST['contenido'] ?? 'Contenido del post...';
?>
<!DOCTYPE html>
<html lang="es-CO" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - <?php echo htmlspecialchars($titulo); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: rgb(173, 75, 19);
            --bs-primary: rgb(173, 75, 19);
            --bs-primary-rgb: 173, 75, 19;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .preview-header {
            background: linear-gradient(135deg, var(--primary-color), #8B4513);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .preview-content {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .post-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
        }
        
        .post-content h1, .post-content h2, .post-content h3, 
        .post-content h4, .post-content h5, .post-content h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .post-content ul, .post-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }
        
        .post-content blockquote {
            border-left: 4px solid var(--primary-color);
            padding-left: 1rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #666;
        }
        
        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .post-content code {
            background-color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        
        .post-content pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1rem 0;
        }
        
        .post-content pre code {
            background: none;
            padding: 0;
        }
        
        .preview-footer {
            background: #343a40;
            color: white;
            padding: 1rem 0;
            text-align: center;
        }
        
        .btn-close-preview {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Close Preview Button -->
    <button type="button" class="btn btn-light btn-close-preview" onclick="window.close()">
        <i class="bi bi-x-lg me-2"></i>Cerrar Vista Previa
    </button>
    
    <!-- Preview Header -->
    <div class="preview-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold">
                        <i class="bi bi-eye me-3"></i>Vista Previa del Post
                    </h1>
                    <p class="lead mb-0">Esta es una vista previa de cómo se verá tu post cuando esté publicado</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Post Content -->
                <div class="preview-content">
                    <h1 class="post-title"><?php echo htmlspecialchars($titulo); ?></h1>
                    
                    <div class="post-meta">
                        <div class="row">
                            <div class="col-md-6">
                                <i class="bi bi-person-circle me-2"></i>
                                <span>Autor del Post</span>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <i class="bi bi-calendar3 me-2"></i>
                                <span><?php echo date('d/m/Y H:i'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($contenido)); ?>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="bi bi-clock me-1"></i>
                                Vista previa generada el <?php echo date('d/m/Y H:i'); ?>
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>Imprimir
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                                <i class="bi bi-x-circle me-1"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Preview Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Información de la Vista Previa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Características mostradas:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check text-success me-2"></i>Formato del título</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Estilo del contenido</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Metadatos del post</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Responsive design</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Notas importantes:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Esta es solo una vista previa</li>
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>No se han guardado cambios</li>
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Las imágenes no se muestran</li>
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Los enlaces no funcionan</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Tip:</strong> Para ver el post completo con imágenes y funcionalidad completa, 
                            guárdalo primero y luego publícalo.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview Footer -->
    <div class="preview-footer">
        <div class="container">
            <p class="mb-0">
                <i class="bi bi-eye me-2"></i>
                Vista Previa - AEDEPORT Blog Manager
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-close after 5 minutes of inactivity
        let inactivityTimer;
        
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if (confirm('¿Deseas cerrar la vista previa? Ha estado inactiva por 5 minutos.')) {
                    window.close();
                } else {
                    resetInactivityTimer();
                }
            }, 300000); // 5 minutes
        }
        
        // Reset timer on user activity
        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('keypress', resetInactivityTimer);
        document.addEventListener('click', resetInactivityTimer);
        
        // Start timer
        resetInactivityTimer();
        
        // Keyboard shortcut to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html> 