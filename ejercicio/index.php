<!DOCTYPE html>
<html lang="es">
<head>

	<!-- Basic Page Needs
	================================================== -->
	<title>Portal de Ejercicio - Videos de Entrenamiento</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="Portal de ejercicio y entrenamiento fitness">
	<meta name="author" content="AbedePort">
	<meta name="keywords" content="ejercicio entrenamiento fitness videos">

	<!-- Favicons
	================================================== -->
	<link rel="shortcut icon" href="../noticias/assets/images/esports/favicons/favicon.ico">
	<link rel="apple-touch-icon" sizes="120x120" href="../noticias/assets/images/esports/favicons/favicon-120.png">
	<link rel="apple-touch-icon" sizes="152x152" href="../noticias/assets/images/esports/favicons/favicon-152.png">

	<!-- Mobile Specific Metas
	================================================== -->
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">

	<!-- Google Web Fonts
	================================================== -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,700;1,400&family=Roboto+Condensed:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

	<!-- CSS
	================================================== -->
	<!-- Vendor CSS -->
	<link href="../noticias/assets/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="../noticias/assets/fonts/font-awesome/css/all.min.css" rel="stylesheet">
	<link href="../noticias/assets/fonts/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
	<link href="../noticias/assets/vendor/magnific-popup/dist/magnific-popup.css" rel="stylesheet">
	<link href="../noticias/assets/vendor/slick/slick.css" rel="stylesheet">

	<!-- Template CSS-->
	<link href="../noticias/assets/css/style-esports.css" rel="stylesheet">

	<!-- Custom CSS-->
	<link href="../noticias/assets/css/custom.css" rel="stylesheet">

	<!-- Exercise Video Styling -->
	<style>
	.video-container {
		position: relative;
		max-width: 100%;
		margin-bottom: 1rem;
		background: #000;
		border-radius: 8px;
		overflow: hidden;
	}
	
	.video-container video {
		width: 100%;
		height: auto;
		display: block;
		background: #000;
	}
	
	.video-fallback {
		padding: 2rem;
		text-align: center;
		background: #f8f9fa;
		border: 2px dashed #dee2e6;
		border-radius: 8px;
	}
	
	.video-fallback a {
		color: #007bff;
		text-decoration: none;
		font-weight: bold;
	}
	
	.video-fallback a:hover {
		text-decoration: underline;
	}
	
	.post--single.mb-4 {
		margin-bottom: 2rem !important;
	}
	
	/* Responsive video adjustments */
	@media (max-width: 768px) {
		.video-container video {
			height: auto;
			min-height: 180px;
		}
	}
	
	@media (min-width: 769px) and (max-width: 991px) {
		.video-container video {
			height: 250px;
		}
	}
	
	/* Equal height cards */
	.h-100 {
		height: 100% !important;
	}
	
	.card.h-100 {
		display: flex;
		flex-direction: column;
	}
	
	.card.h-100 .card__content {
		flex: 1;
	}
	</style>

</head>
<body data-template="template-esports">

	<div class="site-wrapper clearfix">
		<div class="site-overlay"></div>

		<!-- Header
		================================================== -->
		
		<!-- Header Mobile -->
		<div class="header-mobile clearfix" id="header-mobile">
			<div class="header-mobile__logo">
				<a href="../index.html"><img src="../noticias/assets/images/esports/logo.png" srcset="../noticias/assets/images/esports/logo.png" alt="AbedePort" class="header-mobile__logo-img"></a>
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
						<!-- Social Links / End -->
			
						<!-- Account Navigation -->
						<ul class="nav-account">
		
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
							<a href="../index.html"><img src="../noticias/assets/images/esports/logo.png" srcset="../noticias/assets/images/esports/logo.png" alt="AbedePort" class="header-logo__img" height="100px" width="100px"></a>
						</div>
						<!-- Header Logo / End -->
		
						<!-- Main Navigation -->
						<nav class="main-nav">
							<ul class="main-nav__list">
								<li class=""><a href="../noticias/">Inicio</a></li>
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
																	<a href="#"><img src="../noticias/assets/images/samples/nav-post-img-1.jpg" alt=""></a>
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
																	<a href="#"><img src="../noticias/assets/images/samples/nav-post-img-2.jpg" alt=""></a>
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
																	<a href="#"><img src="../noticias/assets/images/samples/nav-post-img-3.jpg" alt=""></a>
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
							<form action="#" id="mobile-search-form" class="search-form">
								<input type="text" class="form-control header-mobile__search-control" value="" placeholder="Buscar ejercicios...">
								<button type="submit" class="header-mobile__search-submit"><i class="fas fa-search"></i></button>
							</form>
						</div>
						<!-- Header Search Form / End -->
		
					</div>
				</div>
			</div>
			<!-- Header Primary / End -->
		
		</header>
		<!-- Header / End -->

		<!-- Page Heading
		================================================== -->
		<div class="page-heading page-heading--horizontal effect-duotone effect-duotone--primary">
			<div class="container">
				<div class="row">
					<div class="col align-self-start">
						<h1 class="page-heading__title">Videos de <span class="highlight">Ejercicio</span></h1>
					</div>
					<div class="col align-self-end">
						<ol class="page-heading__breadcrumb breadcrumb font-italic">
							<li class="breadcrumb-item"><a href="../index.html">Inicio</a></li>
							<li class="breadcrumb-item"><a href="./">Ejercicios</a></li>
							<li class="breadcrumb-item active" aria-current="page">Videos</li>
						</ol>
					</div>
				</div>
			</div>
		</div>
		<!-- Page Heading / End -->
		
		
		<!-- Content
		================================================== -->
		<div class="site-content">
			<div class="container">
		
		
				<!-- Video Playlist block / End -->
		
		
				<!-- Exercise Videos List -->
				<div class="row">
					
					<!-- Exercise Video 1 -->
					<div class="col-lg-6 col-md-6 col-12 mb-4">
						<article class="card post post--single post--single-sm h-100">
							<div class="card__header card__header--no-highlight flex-column align-items-start">
								<div class="post__category">
									<span class="label posts__cat-label posts__cat-label--category-1">Rutinas</span>
									<span class="label posts__cat-label posts__cat-label--category-4">Principiante</span>
								</div>
								<header class="post__header">
									<h2 class="post__title">Rutina de Ejercicios de Brazo - Fortalecimiento y Tonificación</h2>
									<ul class="post__meta meta">
										<li class="meta__item meta__item--date"><time datetime="2024-09-01">1 de Septiembre, 2024</time></li>
										<li class="meta__item meta__item--views">1245</li>
										<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> 89</a></li>
										<li class="meta__item meta__item--comments"><a href="#">12</a></li>
									</ul>
								</header>
							</div>
							<div class="card__content">
								<!-- Video Player -->
								<div class="video-container mb-3">
									<video width="100%" height="300" controls preload="metadata">
										<source src="../video/Brazo.mp4" type="video/mp4">
										Tu navegador no soporta el elemento de video HTML5.
										<p>Si no puedes ver el video, <a href="../video/Brazo.mp4" download>descárgalo aquí</a>.</p>
									</video>
								</div>
							</div>
						</article>
					</div>
					<!-- Exercise Video 1 / End -->
					
					<!-- Exercise Video 2 -->
					<div class="col-lg-6 col-md-6 col-12 mb-4">
						<article class="card post post--single post--single-sm h-100">
							<div class="card__header card__header--no-highlight flex-column align-items-start">
								<div class="post__category">
									<span class="label posts__cat-label posts__cat-label--category-2">Cardio</span>
									<span class="label posts__cat-label posts__cat-label--category-3">Intermedio</span>
								</div>
								<header class="post__header">
									<h2 class="post__title">Cardio Intensivo 1 - Quema Grasa y Mejora tu Resistencia</h2>
									<ul class="post__meta meta">
										<li class="meta__item meta__item--date"><time datetime="2024-09-02">2 de Septiembre, 2024</time></li>
										<li class="meta__item meta__item--views">2156</li>
										<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> 134</a></li>
										<li class="meta__item meta__item--comments"><a href="#">23</a></li>
									</ul>
								</header>
							</div>
							<div class="card__content">
								<!-- Video Player -->
								<div class="video-container mb-3">
									<video width="100%" height="300" controls preload="metadata">
										<source src="../video/Cardio%201.mp4" type="video/mp4">
										Tu navegador no soporta el elemento de video HTML5.
										<p>Si no puedes ver el video, <a href="../video/Cardio%201.mp4" download>descárgalo aquí</a>.</p>
									</video>
								</div>
							</div>
						</article>
					</div>
					<!-- Exercise Video 2 / End -->
					
					<!-- Exercise Video 3 -->
					<div class="col-lg-6 col-md-6 col-12 mb-4">
						<article class="card post post--single post--single-sm h-100">
							<div class="card__header card__header--no-highlight flex-column align-items-start">
								<div class="post__category">
									<span class="label posts__cat-label posts__cat-label--category-1">Fuerza</span>
									<span class="label posts__cat-label posts__cat-label--category-3">Avanzado</span>
								</div>
								<header class="post__header">
									<h2 class="post__title">Rutina de Mancuernas - Entrenamiento de Fuerza Completo</h2>
									<ul class="post__meta meta">
										<li class="meta__item meta__item--date"><time datetime="2024-09-03">3 de Septiembre, 2024</time></li>
										<li class="meta__item meta__item--views">1687</li>
										<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> 98</a></li>
										<li class="meta__item meta__item--comments"><a href="#">19</a></li>
									</ul>
								</header>
							</div>
							<div class="card__content">
								<!-- Video Player -->
								<div class="video-container mb-3">
									<video width="100%" height="300" controls preload="metadata">
										<source src="../video/Mancuernas.mp4" type="video/mp4">
										Tu navegador no soporta el elemento de video HTML5.
										<p>Si no puedes ver el video, <a href="../video/Mancuernas.mp4" download>descárgalo aquí</a>.</p>
									</video>
								</div>
							</div>
						</article>
					</div>
					<!-- Exercise Video 3 / End -->
					
					<!-- Exercise Video 4 -->
					<div class="col-lg-6 col-md-6 col-12 mb-4">
						<article class="card post post--single post--single-sm h-100">
							<div class="card__header card__header--no-highlight flex-column align-items-start">
								<div class="post__category">
									<span class="label posts__cat-label posts__cat-label--category-1">Fuerza</span>
									<span class="label posts__cat-label posts__cat-label--category-3">Intermedio</span>
								</div>
								<header class="post__header">
									<h2 class="post__title">Rutina de Ejercicios de Piernas - Fortalecimiento y Potencia</h2>
									<ul class="post__meta meta">
										<li class="meta__item meta__item--date"><time datetime="2024-09-04">4 de Septiembre, 2024</time></li>
										<li class="meta__item meta__item--views">856</li>
										<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> 67</a></li>
										<li class="meta__item meta__item--comments"><a href="#">15</a></li>
									</ul>
								</header>
							</div>
							<div class="card__content">
								<!-- Video Player -->
								<div class="video-container mb-3">
									<video width="100%" height="300" controls preload="metadata">
										<source src="../video/Pierna.mp4" type="video/mp4">
										Tu navegador no soporta el elemento de video HTML5.
										<p>Si no puedes ver el video, <a href="../video/Pierna.mp4" download>descárgalo aquí</a>.</p>
									</video>
								</div>
							</div>
						</article>
					</div>
					<!-- Exercise Video 4 / End -->
					
					<!-- Exercise Video 5 -->
					<div class="col-lg-6 col-md-6 col-12 mb-4">
						<article class="card post post--single post--single-sm h-100">
							<div class="card__header card__header--no-highlight flex-column align-items-start">
								<div class="post__category">
									<span class="label posts__cat-label posts__cat-label--category-2">Cardio</span>
									<span class="label posts__cat-label posts__cat-label--category-3">Avanzado</span>
								</div>
								<header class="post__header">
									<h2 class="post__title">Cardio Intensivo 3 - Entrenamiento Extremo de Resistencia</h2>
									<ul class="post__meta meta">
										<li class="meta__item meta__item--date"><time datetime="2024-09-05">5 de Septiembre, 2024</time></li>
										<li class="meta__item meta__item--views">1543</li>
										<li class="meta__item meta__item--likes"><a href="#"><i class="meta-like icon-heart"></i> 112</a></li>
										<li class="meta__item meta__item--comments"><a href="#">28</a></li>
									</ul>
								</header>
							</div>
							<div class="card__content">
								<!-- Video Player -->
								<div class="video-container mb-3">
									<video width="100%" height="300" controls preload="metadata">
										<source src="../video/Cardio%203.mp4" type="video/mp4">
										Tu navegador no soporta el elemento de video HTML5.
										<p>Si no puedes ver el video, <a href="../video/Cardio%203.mp4" download>descárgalo aquí</a>.</p>
									</video>
								</div>
							</div>
						</article>
					</div>
					<!-- Exercise Video 5 / End -->
					
				</div>
				<!-- Exercise Videos List / End -->
		
			</div>
		</div>
		
		<!-- Content / End -->
		



	</div>

	<!-- Javascript Files
	================================================== -->
	<!-- Core JS -->
	<script src="../noticias/assets/vendor/jquery/jquery.min.js"></script>
	<script src="../noticias/assets/vendor/jquery/jquery-migrate.min.js"></script>
	<script src="../noticias/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="../noticias/assets/js/core.js"></script>
	
	<!-- Vendor JS -->
	
	<!-- Template JS -->
	<script src="../noticias/assets/js/init.js"></script>
	<script src="../noticias/assets/js/custom.js"></script>
	
	<!-- Video Player Enhancement -->
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Enhance all video players
		const videos = document.querySelectorAll('video');
		
		videos.forEach(function(video, index) {
			// Add loading indicator
			video.addEventListener('loadstart', function() {
				console.log('Loading video ' + (index + 1) + '...');
			});
			
			// Handle successful load
			video.addEventListener('loadeddata', function() {
				console.log('Video ' + (index + 1) + ' loaded successfully');
				video.style.opacity = '1';
			});
			
			// Handle errors
			video.addEventListener('error', function(e) {
				console.error('Error loading video ' + (index + 1) + ':', e);
				const container = video.closest('.video-container');
				if (container) {
					const fallbackHTML = `
						<div class="video-fallback">
							<p><strong>Video no disponible</strong></p>
							<p>Error al cargar el archivo de video.</p>
							<p>Esto puede deberse a:</p>
							<ul style="text-align: left; margin: 1rem 0;">
								<li>Formato de video no compatible</li>
								<li>Archivo corrupto o faltante</li>
								<li>Problema de conexión</li>
							</ul>
							<a href="${video.querySelector('source').src}" download class="btn btn-primary">Descargar video</a>
						</div>
					`;
					container.innerHTML = fallbackHTML;
				}
			});
			
			// Set initial opacity for smooth loading
			video.style.opacity = '0.7';
		});
	});
	</script>

</body>
</html>