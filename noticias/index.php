<?php
session_start();
// Include database connection
require_once '../admin/conn.php';

// Function to fetch news from database
function getLatestNews($conn, $limit = 6) {
    if ($conn === null) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT bp.*, u.nombre, u.apellido 
            FROM blog_posts bp 
            JOIN usuario u ON bp.autor_id = u.id_usuario 
            WHERE bp.estado = 'publicado' 
            ORDER BY bp.fecha_publicacion DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Function to format date in Spanish
function formatSpanishDate($date) {
    $months = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day de $month, $year";
}

// Function to create slug from title
function createSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s_]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Function to truncate text
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Function to determine sport category class
function getSportCategoryClass($etiquetas) {
    if (strpos($etiquetas, 'futbol') !== false) return 'posts__cat-label--category-1';
    if (strpos($etiquetas, 'futsal') !== false) return 'posts__cat-label--category-2';
    if (strpos($etiquetas, 'baloncesto') !== false) return 'posts__cat-label--category-3';
    if (strpos($etiquetas, 'voleibol') !== false) return 'posts__cat-label--category-4';
    return 'posts__cat-label--category-1'; // default
}

// Function to get multiple sport categories from tags
function getSportCategories($etiquetas) {
    $sports = [
        'futbol' => ['name' => 'Fútbol', 'class' => 'category-1'],
        'futsal' => ['name' => 'Futsal', 'class' => 'category-2'], 
        'baloncesto' => ['name' => 'Baloncesto', 'class' => 'category-3'],
        'voleibol' => ['name' => 'Voleibol', 'class' => 'category-4']
    ];
    
    $tags = array_map('trim', explode(',', $etiquetas));
    $categories = [];
    
    foreach ($tags as $tag) {
        $tag = strtolower($tag);
        if (isset($sports[$tag])) {
            $categories[] = $sports[$tag];
        }
    }
    
    // If no sport categories found, add a default
    if (empty($categories)) {
        $categories[] = ['name' => 'Noticias', 'class' => 'category-1'];
    }
    
    return $categories;
}

// Function to get tournament standings
function getTournamentStandings($conn, $tournament_id = null, $limit = 5) {
    if ($conn === null) {
        return [];
    }
    
    try {
        if ($tournament_id) {
            $stmt = $conn->prepare("
                SELECT t.nombre as tournament_name, t.deporte, et.* 
                FROM tournaments t 
                JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
                WHERE t.id_tournament = :tournament_id AND t.estado = 'activo'
                ORDER BY et.posicion ASC 
                LIMIT :limit
            ");
            $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare("
                SELECT t.nombre as tournament_name, t.deporte, et.* 
                FROM tournaments t 
                JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
                WHERE t.estado = 'activo'
                ORDER BY t.id_tournament ASC, et.posicion ASC 
                LIMIT :limit
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        error_log("getTournamentStandings error: " . $e->getMessage());
        return [];
    }
}

// Function to get active tournaments
function getActiveTournaments($conn) {
    if ($conn === null) {
        return [];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT t.*, COUNT(et.id_equipo_tournament) as team_count 
            FROM tournaments t 
            LEFT JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
            WHERE t.estado = 'activo' 
            GROUP BY t.id_tournament 
            ORDER BY team_count DESC, t.fecha_inicio DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get latest news
$latestNews = getLatestNews($conn, 6);

// Get tournament standings (default to first active tournament)
$active_tournaments = getActiveTournaments($conn);
$default_tournament_id = !empty($active_tournaments) ? $active_tournaments[0]['id_tournament'] : 1;
$tournament_standings = getTournamentStandings($conn, $default_tournament_id, 5);
$current_tournament = !empty($active_tournaments) ? $active_tournaments[0] : null;

// Debug information
// error_log("Active tournaments count: " . count($active_tournaments));
// error_log("Tournament standings count: " . count($tournament_standings));
// error_log("Default tournament ID: " . $default_tournament_id);
?>
<!DOCTYPE html>
<html lang="zxx">
<head>

	<!-- Basic Page Needs
	================================================== -->
	<title>Noticias Deportivas - Portal de Noticias del Deporte</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="Portal de noticias deportivas con las últimas novedades del mundo del deporte">
	<meta name="author" content="AbedEport">
	<meta name="keywords" content="noticias deportivas, deportes, fútbol, baloncesto, tenis, atletismo">

	<!-- Favicons
	================================================== -->
	<link rel="shortcut icon" href="assets/images/esports/favicons/favicon.ico">
	<link rel="apple-touch-icon" sizes="120x120" href="assets/images/esports/favicons/favicon-120.png">
	<link rel="apple-touch-icon" sizes="152x152" href="assets/images/esports/favicons/favicon-152.png">

	<!-- Mobile Specific Metas
	================================================== -->
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">

	<!-- Google Web Fonts
	================================================== -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,700;1,400&amp;family&#x3D;Roboto+Condensed:ital,wght@0,400;0,700;1,400;1,700&amp;display&#x3D;swap" rel="stylesheet">

	<!-- CSS
	================================================== -->
	<!-- Vendor CSS -->
	<link href="assets/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="assets/fonts/font-awesome/css/all.min.css" rel="stylesheet">
	<link href="assets/fonts/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
	<link href="assets/vendor/magnific-popup/dist/magnific-popup.css" rel="stylesheet">
	<link href="assets/vendor/slick/slick.css" rel="stylesheet">

	<!-- REVOLUTION STYLE SHEETS -->
	<link rel="stylesheet" type="text/css" href="assets/vendor/revolution/css/settings.css">

	<!-- REVOLUTION LAYERS STYLES -->
	<link rel="stylesheet" type="text/css" href="assets/vendor/revolution/css/layers.css">

	<!-- REVOLUTION NAVIGATION STYLES -->
	<link rel="stylesheet" type="text/css" href="assets/vendor/revolution/css/navigation.css">

	<!-- REVEAL ADD-ON FILES -->
	<link rel='stylesheet' href='assets/vendor/revolution-addons/reveal/css/revolution.addon.revealer.css?ver=1.0.0' type='text/css' media='all' />
	<link rel='stylesheet' href='assets/vendor/revolution-addons/reveal/css/revolution.addon.revealer.preloaders.css?ver=1.0.0' type='text/css' media='all' />

	<!-- TYPEWRITER ADD-ON FILES -->
	<link rel='stylesheet' href='assets/vendor/revolution-addons/typewriter/css/typewriter.css' type='text/css' media='all' />

	<!-- Template CSS-->
	<link href="assets/css/style-esports.css" rel="stylesheet">

	<!-- Custom CSS-->
	<link href="assets/css/custom.css" rel="stylesheet">

</head>
<body data-template="template-esports">

	<div class="site-wrapper clearfix">
		<div class="site-overlay"></div>

		<!-- Header
		================================================== -->
		
		<!-- Header Mobile -->
		<div class="header-mobile clearfix" id="header-mobile">
			<div class="header-mobile__logo">
				<a href="/"><img src="assets/images/esports/logo.png" srcset="assets/images/esports/logo.png 2x" alt="Club Deportivo" class="header-mobile__logo-img" width="100px" height="100px"></a>
			</div>
			<div class="header-mobile__inner">
				<a id="header-mobile__toggle" class="burger-menu-icon"><span class="burger-menu-icon__line"></span></a>
				<span class="header-mobile__search-icon" id="header-mobile__search-icon"></span>
			</div>
		</div>
		<!-- Header Mobile / End -->
		
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
								<a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Twitch"><i class="fab fa-twitch"></i></a>
							</li>
							<li class="social-links__item">
								<a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="YouTube"><i class="fab fa-youtube"></i></a>
							</li>
							<li class="social-links__item">
								<a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Google+"><i class="fab fa-google-plus-g"></i></a>
							</li>
							<li class="social-links__item">
								<a href="#" class="social-links__link" data-toggle="tooltip" data-placement="bottom" title="Instagram"><i class="fab fa-instagram"></i></a>
							</li>
						</ul>
						<!-- Social Links / End -->
			
						<!-- Account Navigation -->
						<ul class="nav-account">
							<?php if (isset($_SESSION['user_id'])): ?>
								<!-- Logged in user navigation -->
								<li class="nav-account__item">
									<a href="account.php">
										<i class="fas fa-user"></i> 
										Hola, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>
									</a>
								</li>
								<li class="nav-account__item nav-account__item--logout">
									<a href="logout.php">
										<i class="fas fa-sign-out-alt"></i> 
										Cerrar Sesión
									</a>
								</li>
							<?php else: ?>
								<!-- Guest navigation -->
								<li class="nav-account__item"><a href="login.php">Iniciar Sesión</a></li>
							<?php endif; ?>
						</ul>
						<!-- Account Navigation / End -->
					</div>
				</div>
			</div>
			<!-- Header Top Bar / End -->
		
			<!-- Header Primary -->
			<div class="header__primary">
				<div class="container">
					<div class="header__primary-inner">
		
						<!-- Header Logo -->
						<div class="header-logo">
							<a href="/noticias/"><img src="assets/images/esports/logo.png" srcset="assets/images/esports/logo.png 2x" alt="Club Deportivo" class="header-logo__img" width="100px" height="100px"></a>
						</div>
						<!-- Header Logo / End -->
		
						<!-- Main Navigation -->
						<nav class="main-nav">
							<ul class="main-nav__list">
								<li class=""><a href="../noticias">Inicio</a></li>
								<li class=""><a href="#">Ejercicios</a>
									<!-- Mega Menu -->
									<div class="main-nav__megamenu">
										<div class="row">
											<div class="col-12">
												<!-- Widget: Featured Videos -->
												<div class="widget widget--megamenu widget-latest-posts">
													<div class="widget__content">
														<ul class="posts posts--simple-list posts-layout-horizontal posts-layout-horizontal--3cols">
															<li class="posts__item posts__item--category-1">
																<figure class="posts__thumb posts__thumb--hover">
																	<a href="/ejercicio"><img src="../noticias/assets/images/samples/nav-post-img-1.jpg" alt=""></a>
																</figure>
																<div class="posts__inner">
																	<div class="posts__cat">
																		<span class="label posts__cat-label posts__cat-label--category-1">Rutinas</span>
																	</div>
																	<h6 class="posts__title posts__title--color-hover"><a href="/ejercicio">Rutina matutina de 15 minutos para principiantes</a></h6>
																	<time datetime="2024-09-01" class="posts__date">1 de Septiembre, 2024</time>
																</div>
															</li>
															<li class="posts__item posts__item--category-2">
																<figure class="posts__thumb posts__thumb--hover">
																	<a href="/ejercicio"><img src="../noticias/assets/images/samples/nav-post-img-2.jpg" alt=""></a>
																</figure>
																<div class="posts__inner">
																	<div class="posts__cat">
																		<span class="label posts__cat-label posts__cat-label--category-2">Cardio</span>
																	</div>
																	<h6 class="posts__title posts__title--color-hover"><a href="/ejercicio">Entrenamiento HIIT de 20 minutos</a></h6>
																	<time datetime="2024-09-02" class="posts__date">2 de Septiembre, 2024</time>
																</div>
															</li>
															<li class="posts__item posts__item--category-3">
																<figure class="posts__thumb posts__thumb--hover">
																	<a href="/ejercicio"><img src="../noticias/assets/images/samples/nav-post-img-3.jpg" alt=""></a>
																</figure>
																<div class="posts__inner">
																	<div class="posts__cat">
																		<span class="label posts__cat-label posts__cat-label--category-3">Fuerza</span>
																	</div>
																	<h6 class="posts__title posts__title--color-hover"><a href="/ejercicio">Entrenamiento de fuerza con peso corporal</a></h6>
																	<time datetime="2024-09-03" class="posts__date">3 de Septiembre, 2024</time>
																</div>
															</li>
														</ul>
													</div>
												</div>
												<!-- Widget: Featured Videos / End -->
											</div>
											<div class="w-100"></div>
								
										</div>
									</div>
									<!-- Mega Menu / End -->
								</li>
							</ul>
						</nav>
						<!-- Main Navigation / End -->
		
						<div class="header__primary-spacer"></div>
		
						<!-- Header Search Form -->
					<div class="header-search-form ">
						<form action="#" id="news-search-form" class="search-form">
							<input type="text" id="news-search-input" class="form-control header-mobile__search-control" value="" placeholder="Buscar noticias...">
							<button type="button" id="search-btn" class="header-mobile__search-submit"><i class="fas fa-search"></i></button>
							<button type="button" id="clear-search-btn" class="header-mobile__search-clear" style="display: none;"><i class="fas fa-times"></i></button>
						</form>
					</div>
						<!-- Header Search Form / End -->
		
		
					</div>
				</div>
			</div>
			<!-- Header Primary / End -->
		
		</header>
		<!-- Header / End -->

		<!-- Hero Unit Slider
		================================================== -->
		<div class="rev_slider_wrapper container" data-source="gallery">
			<div id="hero-revslider_wrapper" class="rev_slider_wrapper" data-alias="funky-slider" data-source="gallery" style="margin:0px auto;background:transparent;padding:0px;margin-top:0px;margin-bottom:0px;">
				<!-- START REVOLUTION SLIDER 5.4.7.2 fullwidth mode -->
				<div id="hero-revslider" class="rev_slider" style="display:none;" data-version="5.4.7.2">
					<ul>
						<!-- Slide #1 -->
						<li data-transition="fade" data-slotamount="default" data-hideafterloop="0" data-hideslideonmobile="off"  data-easein="default" data-easeout="default" data-masterspeed="300" data-rotate="0" data-saveperformance="off" data-title="Slide">
		
							<!-- MAIN IMAGE -->
							<img src="assets/images/esports/hero-slider/hero-bg-1.jpg" data-bgcolor='#1d1429' alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="off" class="rev-slidebg" data-no-retina>
							<!-- LAYERS -->
		
							<!-- LAYER NR. 1 -->
							<div class="tp-caption tp-resizeme rs-parallaxlevel-5"
								id="slide1-layer1"
								data-x="['center','center','center','center']" data-hoffset="['-185','-185','-135','-100']"
								data-y="['bottom','bottom','bottom','bottom']" data-voffset="['0','0','0','0']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="image"
								data-basealign="slide"
								data-responsive_offset="on"
		
								data-frames='[{"delay":800,"speed":1000,"frame":"0","from":"rX:90deg;sX:1;sY:1;skY:10px;opacity:0;","to":"o:1;tO:50% 100%;z:-5;","ease":"Power4.easeOut"},{"delay":"wait","speed":1000,"frame":"999","to":"auto:auto;","ease":"Power3.easeInOut"}]'
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
								style="z-index: 5;">
							</div>
		
							<!-- LAYER NR. 2 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__h alc-hero-slider__h--h1 rs-parallaxlevel-10"
								id="slide1-layer2"
								data-x="['left','left','left','left']" data-hoffset="['775','520','430','320']"
								data-y="['top','top','top','top']" data-voffset="['195','150','120','105']"
								data-fontsize="['90','72','56','42']"
								data-lineheight="['90','72','56','42']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-responsive_offset="on"
		
								data-frames='[{"delay":500,"speed":1000,"frame":"0","from":"x:-200px;sX:1;sY:1;opacity:0;","to":"o:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":650,"frame":"999","to":"x:-200px;opacity:0;","ease":"Power4.easeIn"}]'
		
								style="z-index: 6;">
									<div class="rs-looped rs-wave" data-speed="2" data-angle="0" data-radius="2px" data-origin="50% 50%">Deportes</div>
							</div>
		
							<!-- LAYER NR. 3 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__h alc-hero-slider__h--h1 alc-hero-slider__h--color-primary rs-parallaxlevel-10"
								id="slide1-layer3"
								data-x="['left','left','left','left']" data-hoffset="['775','520','430','320']"
								data-y="['top','top','top','top']" data-voffset="['280','210','175','150']"
								data-fontsize="['90','72','56','42']"
								data-lineheight="['90','72','56','42']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-responsive_offset="on"
		
								data-frames='[{"delay":600,"speed":1000,"frame":"0","from":"x:200px;sX:1;sY:1;opacity:0;","to":"o:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":650,"frame":"999","to":"x:200px;opacity:0;","ease":"Power4.easeIn"}]'
		
								style="z-index: 7;">
									<div class="rs-looped rs-wave" data-speed="2" data-angle="0" data-radius="2px" data-origin="50% 50%">Abejorral</div>
							</div>
		
							<!-- LAYER NR. 4 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__h alc-hero-slider__h--h5 rs-parallaxlevel-11"
								id="slide1-layer4"
								data-x="['left','left','left','left']" data-hoffset="['780','525','435','320']"
								data-y="['top','top','top','top']" data-voffset="['166','130','95','80']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-typewriter='{"lines":"INFORMACIÓN ACTUALIZADA","enabled":"on","speed":"80","delays":"1%7C100","looped":"on","cursorType":"one","blinking":"on","word_delay":"off","sequenced":"on","hide_cursor":"off","start_delay":"500","newline_delay":"1000","deletion_speed":"30","deletion_delay":"3000","blinking_speed":"500","linebreak_delay":"60","cursor_type":"one","background":"off"}'
								data-responsive_offset="on"
		
								data-frames='[{"delay":700,"speed":1000,"frame":"0","from":"y:50px;z:0;rX:45deg;rY:0;rZ:0;sX:0.9;sY:0.9;skX:0;skY:0;opacity:0;","to":"o:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":500,"frame":"999","to":"y:-10px;opacity:0;","ease":"Power3.easeInOut"}]'
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
		
								style="z-index: 8;">
								Portal Deportivo Abejorral
							</div>
		
							<!-- LAYER NR. 5 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__text rs-parallaxlevel-11"
								id="slide1-layer5"
								data-x="['left','left','left','left']" data-hoffset="['780','525','435','320']"
								data-y="['top','top','top','top']" data-voffset="['385','290','250','210']"
								data-fontsize="['14','12','11','10']"
								data-lineheight="['21','18','56','15']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-responsive_offset="on"
		
								data-frames='[{"delay":1000,"speed":1000,"frame":"0","from":"rX:90deg;sX:1;sY:1;opacity:0;","to":"o:1;tO:50% 0%;","ease":"Power4.easeOut"},{"delay":"wait","speed":500,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"},{"frame":"hover","speed":"200","ease":"Power1.easeInOut","to":"o:1;rX:0;rY:0;rZ:0;z:0;"}]'
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
		
								style="z-index: 9;">
								Las noticias más importantes del deporte en Abejorral
							</div>
		
							<!-- LAYER NR. 6 -->
							<div class="tp-caption tp-resizeme rs-parallaxlevel-10"
								id="slide1-layer6"
								data-x="['left','left','left','left']" data-hoffset="['775','525','435','320']"
								data-y="['bottom','bottom','bottom','bottom']" data-voffset="['125','160','105','90']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="button"
								data-basealign="slide"
								data-responsive_offset="on"
		
								data-frames='[{"delay":1500,"speed":1000,"frame":"0","from":"rX:90deg;sX:1;sY:1;opacity:0;","to":"o:1;tO:50% 0%;","ease":"Power4.easeOut"},{"delay":"wait","speed":500,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"},{"frame":"hover","speed":"200","ease":"Power1.easeInOut","to":"o:1;rX:0;rY:0;rZ:0;z:0;"}]'
		
								style="z-index: 10;"><a href="index.php" class="btn btn-primary btn-icon-right">Ver todas las noticias <i class="fas fa-angle-right"></i></a>
							</div>
		
						</li>
						<!-- Slide #1 / End -->
		
						<!-- Slide #2 -->
						<li data-transition="fade" data-slotamount="default" data-hideafterloop="0" data-hideslideonmobile="off"  data-easein="default" data-easeout="default" data-masterspeed="300" data-rotate="0" data-saveperformance="off" data-title="Slide">
		
							<!-- MAIN IMAGE -->
							<img src="assets/images/esports/hero-slider/hero-bg-2.jpg" data-bgcolor='#1d1429' alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="off" class="rev-slidebg" data-no-retina>
							<!-- LAYERS -->
		
							<!-- LAYER NR. 1 -->
							<div class="tp-caption tp-resizeme"
								id="slide2-layer1"
								data-x="['right','right','right','right']" data-hoffset="['0','0','0','0']"
								data-y="['top','top','top','top']" data-voffset="['0','0','0','0']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="image"
								data-basealign="slide"
								data-responsive_offset="on"
		
								data-frames='[{"delay":1200,"speed":800,"frame":"0","from":"rX:90deg;sX:1;sY:1;skY:0;opacity:0;z:1;","to":"o:1;tO:50% 100%;z:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":1000,"frame":"999","to":"auto:auto;","ease":"Power3.easeInOut"}]'
		
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
		
								style="z-index: 1;">
		
							</div>
		
							<!-- LAYER NR. 2 -->
							<div class="tp-caption tp-resizeme rs-parallaxlevel-5"
								id="slide2-layer2"
								data-x="['right','right','right','right']" data-hoffset="['0','0','0','0']"
								data-y="['bottom','bottom','bottom','bottom']" data-voffset="['0','0','0','0']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="image"
								data-basealign="slide"
								data-responsive_offset="on"
		
								data-frames='[{"delay":800,"speed":1000,"frame":"0","from":"rX:90deg;sX:1;sY:1;skY:10px;opacity:0;z:5;","to":"o:1;tO:50% 100%;z:5;","ease":"Power4.easeOut"},{"delay":"wait","speed":1000,"frame":"999","to":"auto:auto;","ease":"Power3.easeInOut"}]'
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
		
								style="z-index: 5;">
		
							</div>
		
							<!-- LAYER NR. 3 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__h alc-hero-slider__h--h1 rs-parallaxlevel-10"
								id="slide2-layer3"
								data-x="['left','left','left','left']" data-hoffset="['120','40','30','20']"
								data-y="['top','top','top','top']" data-voffset="['125','150','120','70']"
								data-fontsize="['90','72','56','42']"
								data-lineheight="['90','72','56','42']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-responsive_offset="on"
		
								data-frames='[{"delay":500,"speed":1000,"frame":"0","from":"x:-200px;sX:1;sY:1;opacity:0;","to":"o:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":650,"frame":"999","to":"x:-200px;opacity:0;","ease":"Power4.easeIn"}]'
		
								style="z-index: 6;">
									<div class="rs-looped rs-wave" data-speed="2" data-angle="0" data-radius="2px" data-origin="50% 50%">Eventos</div>
							</div>
		
							<!-- LAYER NR. 4 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__h alc-hero-slider__h--h1 alc-hero-slider__h--color-primary rs-parallaxlevel-10"
								id="slide2-layer4"
								data-x="['left','left','left','left']" data-hoffset="['120','40','30','20']"
								data-y="['top','top','top','top']" data-voffset="['210','210','175','115']"
								data-fontsize="['90','72','56','42']"
								data-lineheight="['90','72','56','42']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-responsive_offset="on"
		
								data-frames='[{"delay":600,"speed":1000,"frame":"0","from":"x:200px;sX:1;sY:1;opacity:0;","to":"o:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":650,"frame":"999","to":"x:200px;opacity:0;","ease":"Power4.easeIn"}]'
		
								style="z-index: 7;">
									<div class="rs-looped rs-wave" data-speed="2" data-angle="0" data-radius="2px" data-origin="50% 50%">Deportivos</div>
							</div>
		
							<!-- LAYER NR. 5 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__h alc-hero-slider__h--h5 rs-parallaxlevel-11"
								id="slide2-layer5"
								data-x="['left','left','left','left']" data-hoffset="['120','40','30','20']"
								data-y="['top','top','top','top']" data-voffset="['96','130','95','45']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-typewriter='{"lines":"DEPORTES LOCALES","enabled":"on","speed":"80","delays":"1%7C100","looped":"on","cursorType":"one","blinking":"on","word_delay":"off","sequenced":"on","hide_cursor":"off","start_delay":"500","newline_delay":"1000","deletion_speed":"30","deletion_delay":"3000","blinking_speed":"500","linebreak_delay":"60","cursor_type":"one","background":"off"}'
								data-responsive_offset="on"
		
								data-frames='[{"delay":700,"speed":1000,"frame":"0","from":"y:50px;z:0;rX:45deg;rY:0;rZ:0;sX:0.9;sY:0.9;skX:0;skY:0;opacity:0;","to":"o:1;","ease":"Power4.easeOut"},{"delay":"wait","speed":500,"frame":"999","to":"y:-10px;opacity:0;","ease":"Power3.easeInOut"}]'
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
		
								style="z-index: 8;">
								Cobertura completa de eventos
							</div>
		
							<!-- LAYER NR. 6 -->
							<div class="tp-caption tp-resizeme alc-hero-slider__text rs-parallaxlevel-11"
								id="slide2-layer6"
								data-x="['left','left','left','left']" data-hoffset="['120','40','30','20']"
								data-y="['top','top','top','top']" data-voffset="['315','290','250','175']"
								data-fontsize="['14','12','11','10']"
								data-lineheight="['21','18','56','15']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="text"
								data-responsive_offset="on"
		
								data-frames='[{"delay":1000,"speed":1000,"frame":"0","from":"rX:90deg;sX:1;sY:1;opacity:0;","to":"o:1;tO:50% 0%;","ease":"Power4.easeOut"},{"delay":"wait","speed":500,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"},{"frame":"hover","speed":"200","ease":"Power1.easeInOut","to":"o:1;rX:0;rY:0;rZ:0;z:0;"}]'
								data-textAlign="['inherit','inherit','inherit','inherit']"
								data-paddingtop="[0,0,0,0]"
								data-paddingright="[0,0,0,0]"
								data-paddingbottom="[0,0,0,0]"
								data-paddingleft="[0,0,0,0]"
		
								style="z-index: 9;">
								Mantente informado sobre torneos, competencias <br> y actividades deportivas de tu municipio
							</div>
		
							<!-- LAYER NR. 7 -->
							<div class="tp-caption tp-resizeme rs-parallaxlevel-10"
								id="slide2-layer7"
								data-x="['left','left','left','left']" data-hoffset="['120','40','30','20']"
								data-y="['bottom','bottom','bottom','bottom']" data-voffset="['170','160','105','125']"
								data-width="none"
								data-height="none"
								data-whitespace="nowrap"
		
								data-type="button"
								data-basealign="slide"
								data-responsive_offset="on"
		
								data-frames='[{"delay":1500,"speed":1000,"frame":"0","from":"rX:90deg;sX:1;sY:1;opacity:0;","to":"o:1;tO:50% 0%;","ease":"Power4.easeOut"},{"delay":"wait","speed":500,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"},{"frame":"hover","speed":"200","ease":"Power1.easeInOut","to":"o:1;rX:0;rY:0;rZ:0;z:0;"}]'
		
								style="z-index: 10;"><a href="index.php" class="btn btn-primary-inverse btn-icon-right">Explorar eventos <i class="fas fa-angle-right"></i></a>
							</div>
		
						</li>
						<!-- Slide #2 / End -->
		
					</ul>
				</div>
			</div>
			<!-- END REVOLUTION SLIDER -->
		</div>

		
		<!-- Content
		================================================== -->
		<div class="site-content">
			<div class="container">
		
				<div class="row">
					<!-- Content -->
					<div class="content col-lg-8">
		
						<!-- Posts Area #1 -->
						<!-- Search Results Info -->
						<div id="search-results-info" class="search-results-info" style="display: none;">
							<div class="alert alert-info">
								<span id="search-results-text"></span>
								<button type="button" id="clear-search-results" class="btn btn-sm btn-outline-primary ms-2">Limpiar búsqueda</button>
							</div>
						</div>

						<!-- No Results Message -->
						<div id="no-results-message" class="no-results-message" style="display: none;">
							<div class="alert alert-warning text-center">
								<i class="fas fa-search fa-3x mb-3 text-muted"></i>
								<h4>No se encontraron resultados</h4>
								<p>No hay noticias que coincidan con tu búsqueda. Intenta con otros términos.</p>
							</div>
						</div>

						<!-- Posts Grid -->
						<div class="posts posts--cards post-grid post-grid--2cols row" id="news-grid">
		
							<?php if (!empty($latestNews)): ?>
								<?php foreach ($latestNews as $index => $post): ?>
									<?php if ($index >= 15) break; // Limit to 6 posts ?>
									<?php 
										$postSlug = !empty($post['slug']) ? $post['slug'] : createSlug($post['titulo']);
										$defaultImages = [
											'assets/images/esports/samples/post-img1.jpg',
											'assets/images/esports/samples/post-img2.jpg',
											'assets/images/esports/samples/post-img3.jpg',
											'assets/images/esports/samples/post-img4.jpg',
											'assets/images/esports/samples/post-img6.jpg',
											'assets/images/esports/samples/post-img7.jpg'
										];
										$imageUrl = !empty($post['imagen']) ? '../' . $post['imagen'] : $defaultImages[$index % count($defaultImages)];
										$formattedDate = formatSpanishDate($post['fecha_publicacion']);
										$excerpt = truncateText(strip_tags($post['contenido']), 120);
										
										// Get sport categories for this post
										$categories = getSportCategories($post['etiquetas']);
									?>
									<div class="post-grid__item col-sm-6 news-card" 
										 data-title="<?php echo htmlspecialchars(strtolower($post['titulo'])); ?>" 
										 data-content="<?php echo htmlspecialchars(strtolower(strip_tags($post['contenido']))); ?>" 
										 data-tags="<?php echo htmlspecialchars(strtolower($post['etiquetas'])); ?>" 
										 data-author="<?php echo htmlspecialchars(strtolower($post['nombre'] . ' ' . $post['apellido'])); ?>">
										<div class="posts__item posts__item--card posts__item--<?php echo $categories[0]['class']; ?> card">
											<figure class="posts__thumb">
												<div class="posts__cat">
													<?php foreach ($categories as $category): ?>
														<span class="label posts__cat-label posts__cat-label--<?php echo $category['class']; ?>"><?php echo htmlspecialchars($category['name']); ?></span>
													<?php endforeach; ?>
												</div>
												<a href="post.php?slug=<?php echo urlencode($postSlug); ?>"><img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>"></a>
											</figure>
											<div class="posts__inner card__content">
												<a href="post.php?slug=<?php echo urlencode($postSlug); ?>" class="posts__cta"></a>
												<time datetime="<?php echo date('Y-m-d', strtotime($post['fecha_publicacion'])); ?>" class="posts__date"><?php echo $formattedDate; ?></time>
												<h6 class="posts__title posts__title--color-hover"><a href="post.php?slug=<?php echo urlencode($postSlug); ?>"><?php echo htmlspecialchars($post['titulo']); ?></a></h6>
												<div class="posts__excerpt">
													<?php echo htmlspecialchars($excerpt); ?>
												</div>
											</div>
											<footer class="posts__footer card__footer">
												<div class="post-author">
													<figure class="post-author__avatar">
														<img src="assets/images/samples/avatar-<?php echo (($index % 2) == 0) ? '12' : '6'; ?>-xs.jpg" alt="Post Author Avatar">
													</figure>
													<div class="post-author__info">
														<h4 class="post-author__name"><?php echo htmlspecialchars($post['nombre'] . ' ' . $post['apellido']); ?></h4>
													</div>
												</div>
												<ul class="post__meta meta">
													<li class="meta__item meta__item--views"><?php echo rand(500, 3000); ?></li>
													<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> <?php echo rand(50, 500); ?></a></li>
													<li class="meta__item meta__item--comments"><a href="#"><?php echo rand(5, 25); ?></a></li>
												</ul>
											</footer>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
								<!-- Fallback static content -->
								<div class="post-grid__item col-sm-6">
									<div class="posts__item posts__item--card posts__item--category-1 card">
										<figure class="posts__thumb">
											<div class="posts__cat">
												<span class="label posts__cat-label posts__cat-label--category-1">Noticias</span>
											</div>
											<a href="#"><img src="assets/images/esports/samples/post-img1.jpg" alt=""></a>
										</figure>
										<div class="posts__inner card__content">
											<a href="#" class="posts__cta"></a>
											<time datetime="2025-01-26" class="posts__date">26 de enero, 2025</time>
											<h6 class="posts__title posts__title--color-hover"><a href="#">No hay noticias disponibles</a></h6>
											<div class="posts__excerpt">
												Pronto habrá más contenido disponible en nuestro portal de noticias deportivas.
											</div>
										</div>
										<footer class="posts__footer card__footer">
											<div class="post-author">
												<figure class="post-author__avatar">
													<img src="assets/images/samples/avatar-12-xs.jpg" alt="Post Author Avatar">
												</figure>
												<div class="post-author__info">
													<h4 class="post-author__name">Editor</h4>
												</div>
											</div>
											<ul class="post__meta meta">
												<li class="meta__item meta__item--views">0</li>
												<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> 0</a></li>
												<li class="meta__item meta__item--comments"><a href="#">0</a></li>
											</ul>
										</footer>
									</div>
								</div>
							<?php endif; ?>
		
						</div>
						<!-- Post Grid / End -->
						<!-- Posts Area #1 / End -->
		
		
						<!-- Post Grid / End -->
						<!-- Posts Area #2 / End -->
					</div>
					<!-- Content / End -->
		
					<!-- Sidebar -->
					<div id="sidebar" class="sidebar col-lg-4">
		
						<!-- Widget: Standings -->
						<?php if (!empty($active_tournaments)): ?>
							<?php foreach ($active_tournaments as $tournament): ?>
								<?php 
								$tournament_standings = getTournamentStandings($conn, $tournament['id_tournament'], 5); 
								if (!empty($tournament_standings)):
								?>
								<aside class="widget card widget--sidebar widget-standings mb-4">
									<div class="widget__title card__header card__header--has-btn">
										<h4><?php echo htmlspecialchars($tournament['nombre']); ?></h4>
										<a href="standings.php?deporte=<?php echo htmlspecialchars($tournament['deporte']); ?>" class="btn btn-default btn-xs card-header__button">Ver Todas</a>
									</div>
									<div class="widget__content card__content">
										<div class="table-responsive">
											<table class="table table-hover table-standings">
												<thead>
													<tr>
														<th>Posición del Equipo</th>
														<th>Partidos</th>
														<th>Puntos</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($tournament_standings as $team): ?>
													<tr class="<?php echo $team['destacado'] ? 'highlighted' : ''; ?>">
														<td>
															<div class="team-meta">
																<?php if (!empty($team['logo'])): ?>
																<figure class="team-meta__logo">
																	<img src="<?php echo htmlspecialchars($team['logo']); ?>" 
																		 alt="<?php echo htmlspecialchars($team['nombre_equipo']); ?>">
																</figure>
																<?php endif; ?>
																<div class="team-meta__info">
																	<h6 class="team-meta__name"><?php echo htmlspecialchars($team['nombre_equipo']); ?></h6>
																	<span class="team-meta__place"><?php echo htmlspecialchars($team['ciudad'] . ', ' . $team['pais']); ?></span>
																</div>
															</div>
														</td>
														<td><?php echo $team['partidos_jugados']; ?></td>
														<td><?php echo $team['puntos_totales']; ?></td>
													</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</div>
									</div>
								</aside>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php else: ?>
							<aside class="widget card widget--sidebar widget-standings">
								<div class="widget__title card__header">
									<h4>Tabla de Posiciones</h4>
								</div>
								<div class="widget__content card__content">
									<p class="text-center">No hay torneos activos</p>
								</div>
							</aside>
						<?php endif; ?>
						<!-- Widget: Standings / End -->
							</div>
						</aside>
						<!-- Widget: Twitch Streams / End -->
		

					</div>
					<!-- Sidebar / End -->
				</div>
		
			</div>
		</div>
		
		<!-- Content / End -->
		

		<!-- Footer
		================================================== -->
		<footer id="footer" class="footer">
		
		
			<!-- Footer Widgets / End -->
		
			<!-- Footer Social Links -->
			<div class="footer-social">
				<div class="container">
					<ul class="footer-social__list list-unstyled">
						<li class="footer-social__item">
							<a href="#" class="footer-social__link">
								<span class="footer-social__icon">
									<i class="fab fa-facebook"></i>
								</span>
								<div class="footer-social__txt">
									<span class="footer-social__name">Facebook</span>
									<span class="footer-social__user">/alchemistsgaming</span>
								</div>
							</a>
						</li>
						<li class="footer-social__item">
							<a href="#" class="footer-social__link">
								<span class="footer-social__icon">
									<i class="fab fa-twitter"></i>
								</span>
								<div class="footer-social__txt">
									<span class="footer-social__name">Twitter</span>
									<span class="footer-social__user">@alchemistsgaming</span>
								</div>
							</a>
						</li>
						<li class="footer-social__item">
							<a href="#" class="footer-social__link">
								<span class="footer-social__icon">
									<i class="fab fa-twitch"></i>
								</span>
								<div class="footer-social__txt">
									<span class="footer-social__name">Twitch</span>
									<span class="footer-social__user">@alchemistsgaming</span>
								</div>
							</a>
						</li>
						<li class="footer-social__item">
							<a href="#" class="footer-social__link">
								<span class="footer-social__icon">
									<i class="fab fa-youtube"></i>
								</span>
								<div class="footer-social__txt">
									<span class="footer-social__name">YouTube</span>
									<span class="footer-social__user">@alchemistsgaming</span>
								</div>
							</a>
						</li>
						<li class="footer-social__item">
							<a href="#" class="footer-social__link">
								<span class="footer-social__icon">
									<i class="fab fa-google-plus-g"></i>
								</span>
								<div class="footer-social__txt">
									<span class="footer-social__name">Google+</span>
									<span class="footer-social__user">/alchemistsgaming</span>
								</div>
							</a>
						</li>
						<li class="footer-social__item">
							<a href="#" class="footer-social__link">
								<span class="footer-social__icon">
									<i class="fab fa-instagram"></i>
								</span>
								<div class="footer-social__txt">
									<span class="footer-social__name">Instagram</span>
									<span class="footer-social__user">@alchemistsgaming</span>
								</div>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<!-- Footer Social Links / End -->
		</footer>
		<!-- Footer / End -->

	</div>

	<!-- Javascript Files
	================================================== -->
	<!-- Core JS -->
	<script src="assets/vendor/jquery/jquery.min.js"></script>
	<script src="assets/vendor/jquery/jquery-migrate.min.js"></script>
	<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/core.js"></script>
	
	<!-- Vendor JS -->
	
	<!-- REVEAL ADD-ON FILES -->
	<script type='text/javascript' src='assets/vendor/revolution-addons/reveal/js/revolution.addon.revealer.min.js?ver=1.0.0'></script>
	
	<!-- TYPEWRITER ADD-ON FILES -->
	<script type='text/javascript' src='assets/vendor/revolution-addons/typewriter/js/revolution.addon.typewriter.min.js'></script>
	
	<!-- REVOLUTION JS FILES -->
	<script type="text/javascript" src="assets/vendor/revolution/js/jquery.themepunch.tools.min.js"></script>
	<script type="text/javascript" src="assets/vendor/revolution/js/jquery.themepunch.revolution.min.js"></script>
	
	<!-- SLIDER REVOLUTION 5.0 EXTENSIONS  (Load Extensions only on Local File Systems !  The following part can be removed on Server for On Demand Loading) -->
	<script type="text/javascript" src="assets/vendor/revolution/js/extensions/revolution.extension.layeranimation.min.js"></script>
	<script type="text/javascript" src="assets/vendor/revolution/js/extensions/revolution.extension.migration.min.js"></script>
	<script type="text/javascript" src="assets/vendor/revolution/js/extensions/revolution.extension.parallax.min.js"></script>
	<script type="text/javascript" src="assets/vendor/revolution/js/extensions/revolution.extension.slideanims.min.js"></script>
	
	<script type="text/javascript">
		var revapi, tpj;
		(function() {
			if (!/loaded|interactive|complete/.test(document.readyState)) document.addEventListener("DOMContentLoaded", onLoad); else onLoad();
	
			function onLoad() {
				if ( tpj === undefined ) {
					tpj = jQuery;
					if ( "off" == "on" ) {
						tpj.noConflict();
					}
				}
				if ( tpj("#hero-revslider").revolution == undefined ) {
					revslider_showDoubleJqueryError("#hero-revslider");
				} else {
					revapi = tpj("#hero-revslider").show().revolution({
						sliderType: "standard",
						jsFileLocation: "revolution/js/",
						sliderLayout: "auto",
						dottedOverlay: "fourxfour",
						delay: 9000,
						revealer: {
							direction: "tlbr_skew",
							color: "#1d1429",
							duration: "1500",
							delay: "0",
							easing: "Power3.easeOut",
							spinner: "2",
							spinnerColor: "rgba(0,0,0,",
						},
						navigation: {
							keyboardNavigation:"off",
							keyboard_direction: "horizontal",
							mouseScrollNavigation:"off",
							mouseScrollReverse:"default",
							onHoverStop:"off",
							arrows: {
								style: "metis",
								enable: true,
								hide_onmobile: false,
								hide_onleave: false,
								tmp:'',
								left: {
									container: "layergrid",
									h_align: "right",
									v_align: "bottom",
									h_offset: 72,
									v_offset: 0
								},
								right: {
									container: "layergrid",
									h_align: "right",
									v_align: "bottom",
									h_offset: 12,
									v_offset: 0
								}
							}
						},
						responsiveLevels: [1200,992,768,576],
						visibilityLevels: [1200,992,768,576],
						gridwidth: [1420,992,768,576],
						gridheight: [620,580,460,400],
						lazyType:"none",
						parallax: {
							type:"scroll",
							origo:"slidercenter",
							speed:400,
							speedbg:0,
							speedls:0,
							levels:[5,10,15,20,25,30,35,40,45,-10,-15,-20,-25,50,51,55],
						},
						shadow:0,
						spinner:"spinner5",
						stopLoop:"off",
						stopAfterLoops:-1,
						stopAtSlide:-1,
						shuffle:"off",
						autoHeight:"off",
						hideThumbsOnMobile:"off",
						hideSliderAtLimit:0,
						hideCaptionAtLimit:0,
						hideAllCaptionAtLilmit:0,
						debugMode:false,
						fallbacks: {
							simplifyAll:"off",
							nextSlideOnWindowFocus:"off",
							disableFocusListener:false,
						}
					});
				}; /* END OF revapi call */
	
				RsRevealerAddOn(tpj, revapi, "<div class='rsaddon-revealer-spinner rsaddon-revealer-spinner-2'><div class='rsaddon-revealer-2' style='border-top-color: 0.65); border-bottom-color: 0.15); border-left-color: 0.65); border-right-color: 0.15)'><\/div><\/div>");
				RsTypewriterAddOn(tpj, revapi);
	
			}; /* END OF ON LOAD FUNCTION */
		}()); /* END OF WRAPPING FUNCTION */
	</script>
	
	<!-- Template JS -->
	<script src="assets/js/init.js"></script>
	<script src="assets/js/custom.js"></script>

	<!-- News Search Functionality -->
	<script>
		(function() {
			// Search elements
			const searchInput = document.getElementById('news-search-input');
			const searchBtn = document.getElementById('search-btn');
			const clearSearchBtn = document.getElementById('clear-search-btn');
			const clearResultsBtn = document.getElementById('clear-search-results');
			const searchForm = document.getElementById('news-search-form');
			const newsGrid = document.getElementById('news-grid');
			const searchResultsInfo = document.getElementById('search-results-info');
			const searchResultsText = document.getElementById('search-results-text');
			const noResultsMessage = document.getElementById('no-results-message');
			const newsCards = document.querySelectorAll('.news-card');

			// Search state
			let isSearching = false;

			// Search function
			function performSearch(query) {
				query = query.toLowerCase().trim();
				
				if (query === '') {
					clearSearch();
					return;
				}

				isSearching = true;
				let visibleCount = 0;

				newsCards.forEach(card => {
					const title = card.dataset.title || '';
					const content = card.dataset.content || '';
					const tags = card.dataset.tags || '';
					const author = card.dataset.author || '';

					// Check if query matches title, content, tags, or author
					const matches = title.includes(query) || 
								   content.includes(query) || 
								   tags.includes(query) || 
								   author.includes(query);

					if (matches) {
						card.style.display = 'block';
						card.style.animation = 'fadeIn 0.3s ease-in';
						visibleCount++;
					} else {
						card.style.display = 'none';
					}
				});

				// Update search results info
				updateSearchResults(query, visibleCount);
				
				// Show/hide clear button
				clearSearchBtn.style.display = 'inline-block';
			}

			// Update search results display
			function updateSearchResults(query, count) {
				if (count > 0) {
					searchResultsText.textContent = `Se encontraron ${count} resultado${count !== 1 ? 's' : ''} para "${query}"`;
					searchResultsInfo.style.display = 'block';
					noResultsMessage.style.display = 'none';
				} else {
					searchResultsInfo.style.display = 'none';
					noResultsMessage.style.display = 'block';
				}
			}

			// Clear search function
			function clearSearch() {
				isSearching = false;
				searchInput.value = '';
				
				// Show all cards
				newsCards.forEach(card => {
					card.style.display = 'block';
					card.style.animation = 'fadeIn 0.3s ease-in';
				});

				// Hide search UI elements
				searchResultsInfo.style.display = 'none';
				noResultsMessage.style.display = 'none';
				clearSearchBtn.style.display = 'none';
			}

			// Real-time search as user types
			let searchTimeout;
			searchInput.addEventListener('input', function() {
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(() => {
					performSearch(this.value);
				}, 300); // Debounce for 300ms
			});

			// Search button click
			searchBtn.addEventListener('click', function(e) {
				e.preventDefault();
				performSearch(searchInput.value);
			});

			// Clear search button
			clearSearchBtn.addEventListener('click', function(e) {
				e.preventDefault();
				clearSearch();
			});

			// Clear results button
			clearResultsBtn.addEventListener('click', function(e) {
				e.preventDefault();
				clearSearch();
			});

			// Form submission
			searchForm.addEventListener('submit', function(e) {
				e.preventDefault();
				performSearch(searchInput.value);
			});

			// Keyboard shortcuts
			document.addEventListener('keydown', function(e) {
				// Ctrl/Cmd + K to focus search
				if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
					e.preventDefault();
					searchInput.focus();
				}
				
				// Escape to clear search
				if (e.key === 'Escape' && isSearching) {
					clearSearch();
				}
			});

			// Add CSS for animations
			const style = document.createElement('style');
			style.textContent = `
				@keyframes fadeIn {
					from { opacity: 0; transform: translateY(10px); }
					to { opacity: 1; transform: translateY(0); }
				}

				.header-mobile__search-clear {
					background: none;
					border: none;
					color: #666;
					padding: 8px;
					margin-left: 5px;
					cursor: pointer;
					border-radius: 4px;
					transition: all 0.2s ease;
				}

				.header-mobile__search-clear:hover {
					background: #f0f0f0;
					color: #333;
				}

				.search-results-info {
					margin-bottom: 20px;
				}

				.no-results-message {
					margin-bottom: 40px;
				}

				.news-card {
					transition: all 0.3s ease;
				}

				.search-form {
					display: flex;
					align-items: center;
				}

				/* Mobile responsive adjustments */
				@media (max-width: 768px) {
					.search-results-info .btn {
						font-size: 12px;
						padding: 4px 8px;
					}
				}
			`;
			document.head.appendChild(style);

			// Initialize search on page load if there's a URL parameter
			const urlParams = new URLSearchParams(window.location.search);
			const searchQuery = urlParams.get('search');
			if (searchQuery) {
				searchInput.value = searchQuery;
				performSearch(searchQuery);
			}
		})();
	</script>

</body>
</html>
