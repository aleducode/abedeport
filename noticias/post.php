<?php
require_once '../admin/conn.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: ./');
    exit();
}

// Get post data
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
                        <ul class="social-links social-links--inline social-links--main-nav social-links--top-bar">
                            <li class="social-links__item">
                                <a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Facebook"><i class="fab fa-facebook"></i></a>
                            </li>
                            <li class="social-links__item">
                                <a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Twitter"><i class="fab fa-twitter"></i></a>
                            </li>
                            <li class="social-links__item">
                                <a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Instagram"><i class="fab fa-instagram"></i></a>
                            </li>
                        </ul>
                        
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

</body>
</html>