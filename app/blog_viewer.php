<?php
include "conn.php";
include "blog_functions.php";

// Get the slug from URL
$slug = $_GET['slug'] ?? '';

// Fetch posts for portal layout
$all_posts = getPublishedBlogPosts(12);
$featured_post = $all_posts[0] ?? null;
$sidebar_posts = array_slice($all_posts, 1, 3);
$grid_posts = array_slice($all_posts, 4);

if ($slug) {
    // View individual post
    $post = getBlogPostBySlug($slug);
    if (!$post) {
        header("HTTP/1.0 404 Not Found");
        $error_message = "Post no encontrado";
    } else {
        // Get related posts (same tags or recent posts)
        $related_posts = getPublishedBlogPosts(3);
        $related_posts = array_filter($related_posts, function($p) use ($post) {
            return $p['id_post'] != $post['id_post'];
        });
        $related_posts = array_slice($related_posts, 0, 3);
    }
}

// Calculate reading time
function calculateReadingTime($content) {
    $words_per_minute = 200;
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / $words_per_minute);
    return $reading_time;
}

// Format date for display
function formatDate($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "hace {$minutes} min";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "hace {$hours} h";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "hace {$days} días";
    } else {
        return date('d M Y', $timestamp);
    }
}
?>
<!DOCTYPE html>
<html lang="es-CO" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AEDEPORT News</title>
    <meta name="description" content="Noticias y novedades sobre deportes y recreación de AEDEPORT">
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f6fa; }
        .navbar { background: #fff; border-bottom: 1px solid #e5e7eb; }
        .navbar-brand img { height: 40px; }
        .nav-link { color: #222; font-weight: 500; }
        .nav-link.active, .nav-link:hover { color: #1a5f7a !important; }
        .breaking-bar { background: #222; color: #fff; padding: 0.5rem 0; font-weight: 600; }
        .breaking-dot { color: #ff3b3b; font-size: 1.2em; margin-right: 0.5em; }
        .featured-article { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 2rem; }
        .featured-article img { width: 100%; height: 340px; object-fit: cover; }
        .featured-content { padding: 2rem; }
        .featured-title { font-size: 2rem; font-weight: 700; margin-bottom: 1rem; }
        .featured-meta { color: #6b7280; font-size: 0.95rem; margin-bottom: 1rem; }
        .featured-excerpt { color: #444; font-size: 1.1rem; margin-bottom: 1.5rem; }
        .sidebar { background: #fff; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 1.5rem; margin-bottom: 2rem; }
        .sidebar-title { font-size: 1.1rem; font-weight: 700; color: #1a5f7a; margin-bottom: 1rem; }
        .sidebar-news { list-style: none; padding: 0; margin: 0; }
        .sidebar-news li { margin-bottom: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 0.7rem; }
        .sidebar-news li:last-child { border-bottom: none; }
        .sidebar-news .news-title { font-size: 1rem; font-weight: 600; color: #222; text-decoration: none; }
        .sidebar-news .news-meta { color: #6b7280; font-size: 0.85rem; }
        .news-grid .card { border: none; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 2rem; }
        .news-grid .card-img-top { height: 180px; object-fit: cover; border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
        .news-grid .card-title { font-size: 1.1rem; font-weight: 700; }
        .news-grid .card-text { color: #444; font-size: 0.98rem; }
        @media (max-width: 991px) {
            .featured-article img { height: 220px; }
        }
        @media (max-width: 767px) {
            .featured-content { padding: 1rem; }
            .sidebar { padding: 1rem; }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand mx-auto" href="../">
                <img src="../assets/img/logo.png" alt="AEDEPORT Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="?page=blog_viewer">Noticias</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Medellín</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Bogotá</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Cali</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Antioquia</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Deporte</a></li>
                </ul>
                <form class="d-flex ms-3" role="search">
                    <input class="form-control form-control-sm me-2" type="search" placeholder="Buscar noticias..." aria-label="Buscar">
                    <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </nav>
    <!-- Breaking News Bar -->
    <div class="breaking-bar text-center">
        <span class="breaking-dot">●</span> En vivo: Detalles de la última noticia importante aquí.
    </div>

    <?php if (isset($error_message)): ?>
        <div class="container mt-5">
            <div class="alert alert-danger text-center border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-danger me-3"></i>
                    <h4 class="mb-0"><?php echo htmlspecialchars($error_message); ?></h4>
                </div>
                <p class="text-muted mb-3">El artículo que buscas no existe o ha sido eliminado.</p>
                <a href="?page=blog_viewer" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a las Noticias
                </a>
            </div>
        </div>
    <?php elseif (isset($post)): ?>
        <!-- Single Article View -->
        <div class="container mt-4">
            <div class="row">
                <div class="col-lg-8">
                    <article class="single-article">
                        <div class="article-header">
                            <h1 class="article-title"><?php echo htmlspecialchars($post['titulo']); ?></h1>
                            
                            <div class="article-meta">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <span><?php echo htmlspecialchars($post['autor_nombre'] . ' ' . $post['autor_apellido']); ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    <span><?php echo formatDate($post['fecha_publicacion']); ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock me-2"></i>
                                    <span><?php echo calculateReadingTime($post['contenido']); ?> min de lectura</span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($post['imagen']): ?>
                            <img src="<?php echo htmlspecialchars($post['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['titulo']); ?>" 
                                 class="w-100">
                        <?php endif; ?>
                        
                        <div class="article-body">
                            <?php echo nl2br(htmlspecialchars($post['contenido'])); ?>
                            
                            <?php if ($post['etiquetas']): ?>
                                <div class="article-tags mt-4">
                                    <h6 class="text-muted mb-2">Etiquetas:</h6>
                                    <?php
                                    $tags = explode(',', $post['etiquetas']);
                                    foreach ($tags as $tag): ?>
                                        <a href="?page=blog_viewer&tag=<?php echo urlencode(trim($tag)); ?>" 
                                           class="tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                    
                    <!-- Article Actions -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <a href="?page=blog_viewer" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>Volver a las Noticias
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Imprimir
                            </button>
                            <button type="button" class="btn btn-primary" onclick="shareArticle()">
                                <i class="bi bi-share me-2"></i>Compartir
                            </button>
                        </div>
                    </div>
                    
                    <!-- Related Articles -->
                    <?php if (!empty($related_posts)): ?>
                        <div class="related-articles">
                            <h3><i class="bi bi-newspaper me-2"></i>Artículos Relacionados</h3>
                            <div class="row">
                                <?php foreach ($related_posts as $related_post): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="article-card">
                                            <?php if ($related_post['imagen']): ?>
                                                <img src="<?php echo htmlspecialchars($related_post['imagen']); ?>" 
                                                     alt="<?php echo htmlspecialchars($related_post['titulo']); ?>" 
                                                     class="article-image">
                                            <?php endif; ?>
                                            
                                            <div class="article-content">
                                                <h4 class="article-title">
                                                    <a href="?page=blog_viewer&slug=<?php echo htmlspecialchars($related_post['slug']); ?>">
                                                        <?php echo htmlspecialchars($related_post['titulo']); ?>
                                                    </a>
                                                </h4>
                                                
                                                <div class="article-meta">
                                                    <span><?php echo formatDate($related_post['fecha_publicacion']); ?></span>
                                                    <span><?php echo calculateReadingTime($related_post['contenido']); ?> min</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4">
                    <div class="sidebar sticky-top" style="top: 100px;">
                        <h5><i class="bi bi-info-circle me-2"></i>Acerca de AEDEPORT</h5>
                        <p>
                            Somos una organización dedicada al desarrollo deportivo y la recreación. 
                            Nuestro objetivo es promover un estilo de vida saludable a través del deporte.
                        </p>
                        
                        <h5 class="mt-4"><i class="bi bi-link-45deg me-2"></i>Enlaces Útiles</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="../" class="text-decoration-none text-secondary">
                                    <i class="bi bi-house me-2"></i>Página Principal
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="?page=admin_dashboard" class="text-decoration-none text-secondary">
                                    <i class="bi bi-gear me-2"></i>Panel de Administración
                                </a>
                            </li>
                        </ul>
                        
                        <h5 class="mt-4"><i class="bi bi-share me-2"></i>Compartir</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="shareOnSocial('facebook')">
                                <i class="bi bi-facebook"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="shareOnSocial('twitter')">
                                <i class="bi bi-twitter"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="shareOnSocial('linkedin')">
                                <i class="bi bi-linkedin"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="shareOnSocial('whatsapp')">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- News Portal Layout -->
        <div class="container mt-4">
            <div class="row">
                <!-- Main Featured Article -->
                <div class="col-lg-8">
                    <?php if ($featured_post): ?>
                    <div class="featured-article mb-4">
                        <?php if ($featured_post['imagen']): ?>
                            <img src="<?php echo htmlspecialchars($featured_post['imagen']); ?>" alt="<?php echo htmlspecialchars($featured_post['titulo']); ?>">
                        <?php endif; ?>
                        <div class="featured-content">
                            <div class="featured-meta mb-2">
                                <span><i class="bi bi-calendar3 me-1"></i> <?php echo formatDate($featured_post['fecha_publicacion']); ?></span>
                                <span class="ms-3"><i class="bi bi-clock me-1"></i> <?php echo calculateReadingTime($featured_post['contenido']); ?> min</span>
                            </div>
                            <h2 class="featured-title">
                                <a href="?page=blog_viewer&slug=<?php echo htmlspecialchars($featured_post['slug']); ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($featured_post['titulo']); ?>
                                </a>
                            </h2>
                            <div class="featured-excerpt">
                                <?php 
                                $excerpt = strip_tags($featured_post['contenido']);
                                echo htmlspecialchars(substr($excerpt, 0, 180)) . (strlen($excerpt) > 180 ? '...' : '');
                                ?>
                            </div>
                            <a href="?page=blog_viewer&slug=<?php echo htmlspecialchars($featured_post['slug']); ?>" class="btn btn-primary">
                                Leer más
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- News Grid -->
                    <div class="row news-grid">
                        <?php foreach ($grid_posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <?php if ($post['imagen']): ?>
                                    <img src="<?php echo htmlspecialchars($post['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="?page=blog_viewer&slug=<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($post['titulo']); ?>
                                        </a>
                                    </h5>
                                    <div class="featured-meta mb-2">
                                        <span><i class="bi bi-calendar3 me-1"></i> <?php echo formatDate($post['fecha_publicacion']); ?></span>
                                        <span class="ms-3"><i class="bi bi-clock me-1"></i> <?php echo calculateReadingTime($post['contenido']); ?> min</span>
                                    </div>
                                    <p class="card-text">
                                        <?php 
                                        $excerpt = strip_tags($post['contenido']);
                                        echo htmlspecialchars(substr($excerpt, 0, 100)) . (strlen($excerpt) > 100 ? '...' : '');
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Sidebar Últimas Noticias -->
                <div class="col-lg-4">
                    <div class="sidebar sticky-top" style="top: 100px;">
                        <div class="sidebar-title"><i class="bi bi-lightning-charge me-2"></i>Últimas noticias</div>
                        <ul class="sidebar-news">
                            <?php foreach ($sidebar_posts as $post): ?>
                            <li>
                                <a href="?page=blog_viewer&slug=<?php echo htmlspecialchars($post['slug']); ?>" class="news-title">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                                <div class="news-meta">
                                    <?php echo formatDate($post['fecha_publicacion']); ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="?page=blog_viewer" class="btn btn-link p-0 mt-2">Ver noticias recientes <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <footer class="footer mt-5">
        <div class="container py-4 text-center text-muted">
            <div>AEDEPORT News &copy; <?php echo date('Y'); ?> - Todos los derechos reservados</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced sharing functionality
        function shareArticle() {
            const title = '<?php echo isset($post) ? addslashes($post['titulo']) : 'AEDEPORT News'; ?>';
            const text = '<?php echo isset($post) ? addslashes($post['meta_descripcion'] ?? '') : 'Noticias oficiales de AEDEPORT'; ?>';
            const url = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                }).catch(console.error);
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(url).then(function() {
                    showNotification('URL copiada al portapapeles', 'success');
                }).catch(function() {
                    showNotification('Error al copiar URL', 'error');
                });
            }
        }
        
        function shareOnSocial(platform) {
            const title = '<?php echo isset($post) ? addslashes($post['titulo']) : 'AEDEPORT News'; ?>';
            const url = window.location.href;
            const text = '<?php echo isset($post) ? addslashes($post['meta_descripcion'] ?? '') : 'Noticias oficiales de AEDEPORT'; ?>';
            
            let shareUrl = '';
            
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        }
        
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Add smooth scrolling
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Add loading animation to images
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
            });
        });
    </script>
</body>
</html> 