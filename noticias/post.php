<?php
require_once '../admin/conn.php';

// Check if database connection exists
if (!$conn) {
    die('Error: No se pudo conectar a la base de datos. Por favor, verifica la configuración.');
}

// Get slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: ./');
    exit();
}

// Get post data with likes and comments count
try {
    $stmt = $conn->prepare("
        SELECT bp.*, u.nombre, u.apellido
        FROM blog_posts bp
        JOIN usuario u ON bp.autor_id = u.id_usuario
        WHERE bp.slug = ? AND bp.estado = 'publicado'
    ");
    $stmt->bindParam(1, $slug);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: ./');
        exit();
    }

    // Update like and comment counts if they're null (for existing posts)
    if ($post['like_count'] === null || $post['comment_count'] === null) {
        $update_stmt = $conn->prepare("
            UPDATE blog_posts SET
                like_count = (SELECT COUNT(*) FROM post_likes WHERE post_id = ?),
                comment_count = (SELECT COUNT(*) FROM post_comments WHERE post_id = ? AND estado = 'activo')
            WHERE id_post = ?
        ");
        $update_stmt->bindParam(1, $post['id_post'], PDO::PARAM_INT);
        $update_stmt->bindParam(2, $post['id_post'], PDO::PARAM_INT);
        $update_stmt->bindParam(3, $post['id_post'], PDO::PARAM_INT);
        $update_stmt->execute();

        // Refetch the post with updated counts
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    header('Location: ./');
    exit();
}

// Format date in Spanish
function formatSpanishDate($date) {
    $months = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day de $month de $year";
}

// Get related posts
function getRelatedPosts($conn, $current_post_id, $tags, $limit = 2) {
    if (empty($tags)) return [];
    
    $tag_array = explode(',', $tags);
    $tag_conditions = [];
    $params = [];
    
    foreach ($tag_array as $i => $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
            $tag_conditions[] = "bp.etiquetas LIKE ?";
            $params[] = "%$tag%";
        }
    }
    
    if (empty($tag_conditions)) return [];
    
    $sql = "
        SELECT bp.*, u.nombre, u.apellido 
        FROM blog_posts bp 
        JOIN usuario u ON bp.autor_id = u.id_usuario 
        WHERE bp.id_post != ? AND bp.estado = 'publicado' 
        AND (" . implode(' OR ', $tag_conditions) . ")
        ORDER BY bp.fecha_publicacion DESC 
        LIMIT ?
    ";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $current_post_id, PDO::PARAM_INT);
        
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 2, $param);
        }
        
        $stmt->bindValue(count($params) + 2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

$related_posts = getRelatedPosts($conn, $post['id_post'], $post['etiquetas']);

// Function to get multiple sport categories from tags
function getSportCategories($etiquetas) {
    $sports = [
        'futbol' => ['name' => 'Fútbol', 'class' => 'posts__cat-label--category-1'],
        'futsal' => ['name' => 'Futsal', 'class' => 'posts__cat-label--category-2'], 
        'baloncesto' => ['name' => 'Baloncesto', 'class' => 'posts__cat-label--category-3'],
        'voleibol' => ['name' => 'Voleibol', 'class' => 'posts__cat-label--category-4']
    ];
    
    $tags = array_map('trim', explode(',', $etiquetas));
    $result = [];
    
    foreach ($tags as $tag) {
        $tag = strtolower($tag);
        if (isset($sports[$tag])) {
            $result[] = $sports[$tag];
        }
    }
    
    return $result;
}

$sport_categories = getSportCategories($post['etiquetas']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title><?php echo htmlspecialchars($post['titulo']); ?> - AEDEPORT</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo htmlspecialchars($post['meta_descripcion'] ?: substr(strip_tags($post['contenido']), 0, 160)); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($post['nombre'] . ' ' . $post['apellido']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($post['etiquetas']); ?>">
    
    <?php
    // Get current URL
    $current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $site_name = "AEDEPORT";
    $description = htmlspecialchars($post['meta_descripcion'] ?: substr(strip_tags($post['contenido']), 0, 160));
    $image_url = $post['imagen'] ? "https://" . $_SERVER['HTTP_HOST'] . "/" . $post['imagen'] : "https://" . $_SERVER['HTTP_HOST'] . "/assets/img/main_logo.png";
    ?>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['titulo']); ?>">
    <meta property="og:description" content="<?php echo $description; ?>">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:url" content="<?php echo $current_url; ?>">
    <meta property="og:site_name" content="<?php echo $site_name; ?>">
    <meta property="article:author" content="<?php echo htmlspecialchars($post['nombre'] . ' ' . $post['apellido']); ?>">
    <meta property="article:published_time" content="<?php echo date('c', strtotime($post['fecha_publicacion'])); ?>">
    <meta property="article:modified_time" content="<?php echo date('c', strtotime($post['fecha_actualizacion'])); ?>">
    <?php if ($post['etiquetas']): 
        $tags = array_map('trim', explode(',', $post['etiquetas']));
        foreach ($tags as $tag): ?>
    <meta property="article:tag" content="<?php echo htmlspecialchars($tag); ?>">
    <?php endforeach; endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['titulo']); ?>">
    <meta name="twitter:description" content="<?php echo $description; ?>">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">
    <meta name="twitter:url" content="<?php echo $current_url; ?>">
    <meta name="twitter:site" content="@aedeport">
    <meta name="twitter:creator" content="@aedeport">
    
    <!-- Additional Meta Tags for Social Media -->
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    <meta name="twitter:image:alt" content="<?php echo htmlspecialchars($post['titulo']); ?>">
    
    <link rel="shortcut icon" href="../assets/img/sports.png">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,700;1,400&family=Roboto+Condensed:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="assets/fonts/font-awesome/css/all.min.css" rel="stylesheet">
    <link href="assets/fonts/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
    <link href="assets/vendor/magnific-popup/dist/magnific-popup.css" rel="stylesheet">
    <link href="assets/vendor/slick/slick.css" rel="stylesheet">
    <link href="assets/css/style-esports.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">

    <style>
        /* Likes and Comments Styles */
        .post-interactions {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .post-likes-section {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .post-stats {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .like-count, .comment-count-display {
            font-weight: 600;
            color: #495057;
        }

        .post-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-like {
            background: none;
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #495057;
        }

        .btn-like:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .btn-like.liked {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-like.liked i {
            color: white;
        }

        .btn-like i {
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .comments-section h4 {
            margin-bottom: 1.5rem;
            color: #495057;
            font-size: 1.3rem;
        }

        .comment-form-section {
            margin-bottom: 2rem;
        }

        .comment-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .comment-input {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.75rem;
            resize: vertical;
            min-height: 80px;
        }

        .comment-input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .comment-form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }

        .char-count {
            font-size: 0.8rem;
        }

        .comment-item {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .comment-author {
            font-weight: 600;
            color: #495057;
        }

        .comment-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .comment-content {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        .comment-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .comment-actions button {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 0.85rem;
            padding: 0;
        }

        .comment-actions button:hover {
            text-decoration: underline;
        }

        .reply-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .reply-input {
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .replies {
            margin-left: 2rem;
            margin-top: 1rem;
        }

        .reply-item {
            background: #f8f9fa;
            border-left: 3px solid #007bff;
        }

        .loading-comments, .no-comments {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            border: 1px solid transparent;
            color: white;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        @media (max-width: 768px) {
            .post-interactions {
                margin: 1rem -15px 0;
                padding: 1rem 15px;
                border-radius: 0;
            }

            .replies {
                margin-left: 1rem;
            }

            .comment-form-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body data-template="template-esports">

    <div class="site-wrapper clearfix">
        <div class="site-overlay"></div>

        <!-- Header Mobile -->
        <div class="header-mobile clearfix" id="header-mobile">
            <div class="header-mobile__logo">
                <a href="./"><img src="assets/images/logo.png" alt="AEDEPORT" class="header-mobile__logo-img"></a>
            </div>
            <div class="header-mobile__inner">
                <a id="header-mobile__toggle" class="burger-menu-icon"><span class="burger-menu-icon__line"></span></a>
                <span class="header-mobile__search-icon" id="header-mobile__search-icon"></span>
            </div>
        </div>

        <!-- Header Desktop -->
        <header class="header header--layout-3">
            <!-- Header Top Bar -->
            <div class="header__top-bar clearfix">
                <div class="container">
                    <div class="header__top-bar-inner">
                        <!-- Social Links -->
                        
                        <!-- Account Navigation -->
                        <ul class="nav-account">
                            <li class="nav-account__item"><a href="../admin">Iniciar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Header Primary -->
            <div class="header__primary">
                <div class="container">
                    <div class="header__primary-inner">
                        <!-- Header Logo -->
                        <div class="header-logo">
                            <a href="./"><img src="assets/images/esports/logo.png" alt="AEDEPORT" class="header-logo__img" width="100px" height="100px"></a>
                        </div>

                        <!-- Main Navigation -->
                        <nav class="main-nav">
                            <ul class="main-nav__list">
                                <li><a href="../">Inicio</a></li>
                                <li class="current"><a href="./">Noticias</a></li>
                            </ul>
                        </nav>

                        <div class="header__primary-spacer"></div>

                        <!-- Header Search Form -->
                        <div class="header-search-form">
                            <form action="./" method="GET" class="search-form">
                                <input type="text" name="search" class="form-control header-mobile__search-control" placeholder="Buscar noticias...">
                                <button type="submit" class="header-mobile__search-submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Heading -->

        <!-- Content -->
        <div class="site-content">
            <div class="container">
                <div class="row">
                    <!-- Content -->
                    <div class="content col-lg-6 offset-lg-3">
                        <!-- Article -->
                        <article class="post post--single">
                            
                            <?php if (!empty($sport_categories)): ?>
                            <div class="post__category">
                                <?php foreach ($sport_categories as $category): ?>
                                <span class="label posts__cat-label <?php echo $category['class']; ?>"><?php echo $category['name']; ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <header class="post__header">
                                <h2 class="post__title"><?php echo htmlspecialchars($post['titulo']); ?></h2>
                                <ul class="post__meta meta">
                                    <li class="meta__item meta__item--author">
                                        <img src="assets/images/samples/avatar-<?php echo (($post['autor_id'] % 12) + 1); ?>-xs.jpg" alt="Avatar"> 
                                        por <?php echo htmlspecialchars($post['nombre'] . ' ' . $post['apellido']); ?>
                                    </li>
                                    <li class="meta__item meta__item--date">
                                        <time datetime="<?php echo date('Y-m-d', strtotime($post['fecha_publicacion'])); ?>">
                                            <?php echo formatSpanishDate($post['fecha_publicacion']); ?>
                                        </time>
                                    </li>
                                </ul>
                            </header>

                            <div class="post__content-wrapper">
                                <!-- Post Sharing Buttons -->
                                <div class="post-sharing-compact stacked">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" class="btn btn-default btn-sm btn-facebook btn-icon"><i class="fab fa-facebook"></i></a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['titulo']); ?>" 
                                       target="_blank" class="btn btn-default btn-sm btn-twitter btn-icon"><i class="fab fa-twitter"></i></a>
                                    <a href="https://www.instagram.com/" target="_blank" class="btn btn-default btn-sm btn-instagram btn-icon"><i class="fab fa-instagram"></i></a>
                                </div>
                                
                                <div class="post__content">
                                    <div class="post__content--inner-left">
                                        <?php if ($post['imagen']): ?>
                                        <figure class="aligncenter">
                                            <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                                        </figure>
                                        <div class="spacer"></div>
                                        <?php endif; ?>
                                        
                                        <?php echo $post['contenido']; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($post['etiquetas']): ?>
                            <footer class="post__footer">
                                <div class="post__tags post__tags--simple">
                                    <?php 
                                    $tags = array_map('trim', explode(',', $post['etiquetas']));
                                    foreach ($tags as $tag): 
                                    ?>
                                    <a href="./?tag=<?php echo urlencode($tag); ?>"><?php echo htmlspecialchars(ucfirst($tag)); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </footer>
                            <?php endif; ?>

                        </article>
                        <!-- Article / End -->

                        <!-- Likes and Comments Section -->
                        <div class="post-interactions">
                            <!-- Like Section -->
                            <div class="post-likes-section">
                                <div class="post-stats">
                                    <span class="like-count"><?php echo intval($post['like_count'] ?? 0); ?></span>
                                    <span class="like-text">me gusta</span>
                                    <span class="comment-count-display"><?php echo intval($post['comment_count'] ?? 0); ?></span>
                                    <span class="comment-text">comentarios</span>
                                </div>
                                <div class="post-actions">
                                    <button class="btn-like" data-post-id="<?php echo $post['id_post']; ?>">
                                        <i class="far fa-heart"></i>
                                        <span class="like-btn-text">Me gusta</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Comments Section -->
                            <div class="comments-section" id="comments">
                                <h4>Comentarios</h4>

                                <!-- Comment Form -->
                                <div class="comment-form-section">
                                    <form class="comment-form" data-post-id="<?php echo $post['id_post']; ?>">
                                        <div class="form-group">
                                            <textarea class="form-control comment-input" placeholder="Escribe tu comentario..." rows="3" maxlength="1000"></textarea>
                                        </div>
                                        <div class="comment-form-actions">
                                            <small class="text-muted char-count">0/1000 caracteres</small>
                                            <button type="submit" class="btn btn-primary">Comentar</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Comments List -->
                                <div class="comments-list">
                                    <div class="loading-comments" style="display: none;">
                                        <p>Cargando comentarios...</p>
                                    </div>
                                    <div class="no-comments" style="display: none;">
                                        <p>Sé el primero en comentar este artículo.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Likes and Comments Section / End -->
                    </div>
                    <!-- Content / End -->
                </div>
            </div>
        </div>
        <!-- Content / End -->

        <?php if (!empty($related_posts)): ?>
        <!-- Posts Related -->
        <div class="section section--no-paddings">
            <div class="post-related">
                <div class="posts posts--tile posts--tile-alt posts--tile-alt-noborder post-grid row no-gutters">
                    <?php foreach ($related_posts as $related): ?>
                    <div class="post-grid__item col-sm-6">
                        <div class="posts__item posts__item--tile posts__item--category-1 card">
                            <figure class="posts__thumb">
                                <?php if ($related['imagen']): ?>
                                <img src="../<?php echo htmlspecialchars($related['imagen']); ?>" alt="<?php echo htmlspecialchars($related['titulo']); ?>">
                                <?php else: ?>
                                <img src="assets/images/samples/post-img-placeholder.jpg" alt="<?php echo htmlspecialchars($related['titulo']); ?>">
                                <?php endif; ?>
                                <div class="posts__inner">
                                    <?php 
                                    $related_categories = getSportCategories($related['etiquetas']);
                                    if (!empty($related_categories)): 
                                    ?>
                                    <div class="posts__cat">
                                        <?php foreach ($related_categories as $cat): ?>
                                        <span class="label posts__cat-label <?php echo $cat['class']; ?>"><?php echo $cat['name']; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <h6 class="posts__title posts__title--color-hover">
                                        <a href="post.php?slug=<?php echo urlencode($related['slug']); ?>">
                                            <?php echo htmlspecialchars($related['titulo']); ?>
                                        </a>
                                    </h6>
                                    <time datetime="<?php echo date('Y-m-d', strtotime($related['fecha_publicacion'])); ?>" class="posts__date">
                                        <?php echo formatSpanishDate($related['fecha_publicacion']); ?>
                                    </time>
                                </div>
                            </figure>
                            <a href="post.php?slug=<?php echo urlencode($related['slug']); ?>" class="posts__cta"></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Posts Related / End -->
        <?php endif; ?>

        <!-- Footer -->
        <footer id="footer" class="footer">
            <!-- Footer Widgets -->
            <div class="footer-widgets effect-duotone effect-duotone--base">
                <div class="footer-widgets__inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="footer-col-inner">
                                            <aside class="widget widget--footer widget_nav_menu">
                                                <h4 class="widget__title">Enlaces Útiles</h4>
                                                <div class="widget__content">
                                                    <ul class="widget__list">
                                                        <li><a href="../">Inicio</a></li>
                                                        <li><a href="./">Noticias</a></li>
                                                        <li><a href="../admin">Administración</a></li>
                                                    </ul>
                                                </div>
                                            </aside>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-6">
                                        <div class="footer-col-inner">
                                            <aside class="widget widget--footer widget_nav_menu">
                                                <h4 class="widget__title">Deportes</h4>
                                                <div class="widget__content">
                                                    <ul class="widget__list">
                                                        <li><a href="./?tag=futbol">Fútbol</a></li>
                                                        <li><a href="./?tag=futsal">Futsal</a></li>
                                                        <li><a href="./?tag=baloncesto">Baloncesto</a></li>
                                                        <li><a href="./?tag=voleibol">Voleibol</a></li>
                                                    </ul>
                                                </div>
                                            </aside>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-6">
                                <div class="footer-col-inner">
                                    <aside class="widget widget--footer widget_pages">
                                        <h4 class="widget__title">Sobre AEDEPORT</h4>
                                        <div class="widget__content">
                                            <p>Somos una organización dedicada al desarrollo deportivo y la recreación. Promovemos un estilo de vida saludable a través del deporte.</p>
                                        </div>
                                    </aside>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer Widgets / End -->

            <!-- Footer Social Links -->
            <div class="footer-social">
                <div class="container">
                    <ul class="footer-social__list list-unstyled">
                        <li class="footer-social__item">
                            <a href="#" class="footer-social__link">
                                <span class="footer-social__icon"><i class="fab fa-facebook"></i></span>
                                <div class="footer-social__txt">
                                    <span class="footer-social__name">Facebook</span>
                                    <span class="footer-social__user">/aedeport</span>
                                </div>
                            </a>
                        </li>
                        <li class="footer-social__item">
                            <a href="#" class="footer-social__link">
                                <span class="footer-social__icon"><i class="fab fa-twitter"></i></span>
                                <div class="footer-social__txt">
                                    <span class="footer-social__name">Twitter</span>
                                    <span class="footer-social__user">@aedeport</span>
                                </div>
                            </a>
                        </li>
                        <li class="footer-social__item">
                            <a href="#" class="footer-social__link">
                                <span class="footer-social__icon"><i class="fab fa-instagram"></i></span>
                                <div class="footer-social__txt">
                                    <span class="footer-social__name">Instagram</span>
                                    <span class="footer-social__user">@aedeport</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>

    <!-- Javascript Files -->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/jquery/jquery-migrate.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/core.js"></script>
    <script src="assets/js/init.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
        // Likes and Comments Functionality
        $(document).ready(function() {
            const postId = <?php echo $post['id_post']; ?>;
            let userLiked = false;

            // Initialize likes and comments
            initializeLikes();
            loadComments();

            // Character counter for comment form
            $('.comment-input').on('input', function() {
                const length = $(this).val().length;
                $(this).closest('.comment-form').find('.char-count').text(length + '/1000 caracteres');
            });

            // Like button functionality
            $('.btn-like').on('click', function() {
                toggleLike();
            });

            // Comment form submission
            $('.comment-form').on('submit', function(e) {
                e.preventDefault();
                submitComment($(this));
            });

            // Functions
            function initializeLikes() {
                $.get('api/likes.php', { post_id: postId })
                    .done(function(response) {
                        if (response.success !== false) {
                            $('.like-count').text(response.like_count);
                            userLiked = response.liked;
                            updateLikeButton();
                        }
                    })
                    .fail(function() {
                        // User not logged in, hide like button or show login prompt
                        $('.btn-like').hide();
                    });
            }

            function toggleLike() {
                $.ajax({
                    url: 'api/likes.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ post_id: postId })
                })
                .done(function(response) {
                    if (response.success) {
                        $('.like-count').text(response.like_count);
                        userLiked = response.liked;
                        updateLikeButton();
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON;
                    if (xhr.status === 401) {
                        alert('Debes iniciar sesión para dar me gusta a las publicaciones.');
                        window.location.href = '../admin/';
                    } else {
                        alert(response?.error || 'Error al procesar el me gusta');
                    }
                });
            }

            function updateLikeButton() {
                const $btn = $('.btn-like');
                const $icon = $btn.find('i');
                const $text = $btn.find('.like-btn-text');

                if (userLiked) {
                    $btn.addClass('liked');
                    $icon.removeClass('far').addClass('fas');
                    $text.text('Me gusta');
                } else {
                    $btn.removeClass('liked');
                    $icon.removeClass('fas').addClass('far');
                    $text.text('Me gusta');
                }
            }

            function loadComments() {
                $('.loading-comments').show();
                $('.no-comments').hide();

                $.get('api/comments.php', { post_id: postId })
                    .done(function(response) {
                        $('.loading-comments').hide();
                        if (response.success && response.comments.length > 0) {
                            displayComments(response.comments);
                            $('.comment-count-display').text(response.total_count);
                        } else {
                            $('.no-comments').show();
                        }
                    })
                    .fail(function() {
                        $('.loading-comments').hide();
                        $('.comments-list').html('<p class="text-danger">Error al cargar comentarios.</p>');
                    });
            }

            function displayComments(comments) {
                const $commentsList = $('.comments-list');
                $commentsList.empty();

                comments.forEach(function(comment) {
                    const commentHtml = createCommentHtml(comment);
                    $commentsList.append(commentHtml);
                });

                // Bind reply functionality
                bindReplyEvents();
            }

            function createCommentHtml(comment) {
                let repliesHtml = '';
                if (comment.replies && comment.replies.length > 0) {
                    repliesHtml = '<div class="replies">';
                    comment.replies.forEach(function(reply) {
                        repliesHtml += createCommentHtml(reply, true);
                    });
                    repliesHtml += '</div>';
                }

                const isReply = arguments[1] || false;
                const commentClass = isReply ? 'comment-item reply-item' : 'comment-item';

                return `
                    <div class="${commentClass}" data-comment-id="${comment.id_comment}">
                        <div class="comment-header">
                            <span class="comment-author">${comment.nombre} ${comment.apellido}</span>
                            <span class="comment-date">${comment.fecha_comentario_formatted}</span>
                        </div>
                        <div class="comment-content">${comment.contenido}</div>
                        <div class="comment-actions">
                            ${!isReply ? '<button class="reply-btn" data-comment-id="' + comment.id_comment + '">Responder</button>' : ''}
                        </div>
                        ${repliesHtml}
                    </div>
                `;
            }

            function bindReplyEvents() {
                $('.reply-btn').off('click').on('click', function() {
                    const commentId = $(this).data('comment-id');
                    const $comment = $(this).closest('.comment-item');

                    // Remove any existing reply forms
                    $('.reply-form').remove();

                    // Add reply form
                    const replyFormHtml = `
                        <div class="reply-form">
                            <form class="reply-form-element" data-parent-id="${commentId}">
                                <textarea class="reply-input" placeholder="Escribe tu respuesta..." rows="2" maxlength="1000"></textarea>
                                <div class="comment-form-actions">
                                    <small class="text-muted reply-char-count">0/1000 caracteres</small>
                                    <div>
                                        <button type="button" class="btn btn-secondary btn-sm cancel-reply">Cancelar</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Responder</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    `;

                    $comment.append(replyFormHtml);

                    // Focus on the reply input
                    $comment.find('.reply-input').focus();

                    // Bind character counter for reply
                    $comment.find('.reply-input').on('input', function() {
                        const length = $(this).val().length;
                        $comment.find('.reply-char-count').text(length + '/1000 caracteres');
                    });

                    // Bind cancel button
                    $comment.find('.cancel-reply').on('click', function() {
                        $('.reply-form').remove();
                    });

                    // Bind reply form submission
                    $comment.find('.reply-form-element').on('submit', function(e) {
                        e.preventDefault();
                        submitReply($(this));
                    });
                });
            }

            function submitComment($form) {
                const content = $form.find('.comment-input').val().trim();

                if (!content) {
                    alert('Por favor escribe un comentario.');
                    return;
                }

                const $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true).text('Enviando...');

                $.ajax({
                    url: 'api/comments.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        post_id: postId,
                        contenido: content
                    })
                })
                .done(function(response) {
                    if (response.success) {
                        $form.find('.comment-input').val('');
                        $form.find('.char-count').text('0/1000 caracteres');
                        loadComments(); // Reload comments to show the new one
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON;
                    if (xhr.status === 401) {
                        alert('Debes iniciar sesión para comentar.');
                        window.location.href = '../noticias/login.php';
                    } else {
                        alert(response?.error || 'Error al enviar comentario');
                    }
                })
                .always(function() {
                    $submitBtn.prop('disabled', false).text('Comentar');
                });
            }

            function submitReply($form) {
                const content = $form.find('.reply-input').val().trim();
                const parentId = $form.data('parent-id');

                if (!content) {
                    alert('Por favor escribe una respuesta.');
                    return;
                }

                const $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true).text('Enviando...');

                $.ajax({
                    url: 'api/comments.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        post_id: postId,
                        contenido: content,
                        parent_comment_id: parentId
                    })
                })
                .done(function(response) {
                    if (response.success) {
                        $('.reply-form').remove();
                        loadComments(); // Reload comments to show the new reply
                    }
                })
                .fail(function(xhr) {
                    const response = xhr.responseJSON;
                    if (xhr.status === 401) {
                        alert('Debes iniciar sesión para responder.');
                        window.location.href = '../admin/';
                    } else {
                        alert(response?.error || 'Error al enviar respuesta');
                    }
                })
                .always(function() {
                    $submitBtn.prop('disabled', false).text('Responder');
                });
            }
        });
    </script>

</body>
</html>