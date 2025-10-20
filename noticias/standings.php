<?php
// Session and error handling
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/conn.php';

// Function to get tournament standings (with no limit for full standings page)
function getTournamentStandings($conn, $tournament_id = null, $limit = null) {
    if ($conn === null) {
        return [];
    }
    
    try {
        if ($tournament_id) {
            $limitClause = $limit ? "LIMIT :limit" : "";
            $stmt = $conn->prepare("
                SELECT t.nombre as tournament_name, t.deporte, et.* 
                FROM tournaments t 
                JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
                WHERE t.id_tournament = :tournament_id AND t.estado = 'activo'
                ORDER BY et.posicion ASC 
                $limitClause
            ");
            $stmt->bindParam(':tournament_id', $tournament_id, PDO::PARAM_INT);
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
        } else {
            $limitClause = $limit ? "LIMIT :limit" : "";
            $stmt = $conn->prepare("
                SELECT t.nombre as tournament_name, t.deporte, et.* 
                FROM tournaments t 
                JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
                WHERE t.estado = 'activo'
                ORDER BY t.id_tournament ASC, et.posicion ASC 
                $limitClause
            ");
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching tournament standings: " . $e->getMessage());
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
        error_log("Error fetching tournaments: " . $e->getMessage());
        return [];
    }
}

// Check if a specific sport is requested
$selected_sport = isset($_GET['deporte']) ? $_GET['deporte'] : null;

// Get all active tournaments for complete standings page
$active_tournaments = getActiveTournaments($conn);

// Filter tournaments by sport if requested
if ($selected_sport) {
    $active_tournaments = array_filter($active_tournaments, function($tournament) use ($selected_sport) {
        return strtolower($tournament['deporte']) === strtolower($selected_sport);
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

	<!-- Basic Page Needs
	================================================== -->
	<title>Tablas de Posiciones - AbeJorral Abedeport</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="Todas las tablas de posiciones de los torneos de AbeJorral Abedeport">
	<meta name="author" content="AbeJorral Abedeport">
	<meta name="keywords" content="futbol, futsal, baloncesto, voleibol, posiciones, torneos">

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

	<!-- Template CSS-->
	<link href="assets/css/style-esports.css" rel="stylesheet">

	<!-- Custom CSS-->
	<link href="assets/css/custom.css" rel="stylesheet">
	

	<style>
	/* Print Styles */
	@media print {
		/* Hide everything except the table being printed */
		body * {
			visibility: hidden;
		}
		
		.print-area, .print-area * {
			visibility: visible;
		}
		
		.print-area {
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
		}
		
		/* Hide navigation, filters, footer, etc. */
		.header, .footer, .page-heading, .content-filter,
		.sponsors-wrapper, .breadcrumb, .print-table-btn,
		.badge, .site-overlay {
			display: none !important;
		}
		
		/* Clean table styling for print */
		.table {
			border-collapse: collapse;
			width: 100%;
			font-size: 10pt;
		}
		
		.table th, .table td {
			border: 1px solid #000;
			padding: 8px;
		}
		
		.table thead {
			background-color: #f0f0f0;
		}
		
		.card__header h4 {
			margin-bottom: 15px;
			font-size: 14pt;
			font-weight: bold;
		}
		
		/* Remove shadows and borders */
		.card {
			box-shadow: none;
			border: none;
		}
		
		/* Make sure logos print */
		.team-meta__logo img {
			max-width: 30px;
			max-height: 30px;
		}
		
		/* Page break control */
		.card {
			page-break-after: always;
		}
		
		.card:last-child {
			page-break-after: auto;
		}
	}
	
	/* Print button styling */
	.print-table-btn {
		cursor: pointer;
		transition: all 0.3s ease;
	}
	
	.print-table-btn:hover {
		transform: translateY(-2px);
	}
	</style>
	<!-- Override preloader CSS for this page -->
	<style>
	body.page-loader-disable {
		display: block !important;
	}
	</style>

</head>
<body data-template="template-esports" class="page-loader-disable">

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
			
						<!-- Account Navigation -->
						<ul class="nav-account">
							<?php if (isset($_SESSION['user_id'])): ?>
								<li class="nav-account__item nav-account__item--profile">
									<a href="/admin/">
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
								<li class="nav-account__item nav-account__item--login"><a href="/admin/">Iniciar Sesión</a></li>
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
                    </div>
                </div>
            </div>
			<!-- Header Primary / End -->
		
		</header>
		<!-- Header / End -->

		<!-- Page Title -->
		<div class="page-heading page-heading--horizontal effect-duotone effect-duotone--primary">
			<div class="container">
				<div class="row">
					<div class="col align-self-start">
						<?php if ($selected_sport): ?>
							<h1 class="page-heading__title">Posiciones de <span class="highlight"><?php echo ucfirst(htmlspecialchars($selected_sport)); ?></span></h1>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
									<li class="breadcrumb-item"><a href="standings.php">Posiciones</a></li>
									<li class="breadcrumb-item active" aria-current="page"><?php echo ucfirst(htmlspecialchars($selected_sport)); ?></li>
								</ol>
							</nav>
						<?php else: ?>
							<h1 class="page-heading__title">Tablas de <span class="highlight">Posiciones</span></h1>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
									<li class="breadcrumb-item active" aria-current="page">Posiciones</li>
								</ol>
							</nav>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<!-- Page Title / End -->

		<!-- Sports Filter -->
		<nav class="content-filter content-filter--boxed content-filter--highlight-side content-filter--label-left">
			<div class="container">
				<div class="content-filter__inner">
					<a href="#" class="content-filter__toggle"></a>
					<ul class="content-filter__list">
						<li class="content-filter__item <?php echo !$selected_sport ? 'content-filter__item--active' : ''; ?>">
							<a href="standings.php" class="content-filter__link">
								<small>Ver Todos</small>Todas las Posiciones
							</a>
						</li>
						<li class="content-filter__item <?php echo $selected_sport === 'futbol' ? 'content-filter__item--active' : ''; ?>">
							<a href="standings.php?deporte=futbol" class="content-filter__link">
								<small>Deporte</small>Fútbol
							</a>
						</li>
						<li class="content-filter__item <?php echo $selected_sport === 'futsal' ? 'content-filter__item--active' : ''; ?>">
							<a href="standings.php?deporte=futsal" class="content-filter__link">
								<small>Deporte</small>Futsal
							</a>
						</li>
						<li class="content-filter__item <?php echo $selected_sport === 'baloncesto' ? 'content-filter__item--active' : ''; ?>">
							<a href="standings.php?deporte=baloncesto" class="content-filter__link">
								<small>Deporte</small>Baloncesto
							</a>
						</li>
						<li class="content-filter__item <?php echo $selected_sport === 'voleibol' ? 'content-filter__item--active' : ''; ?>">
							<a href="standings.php?deporte=voleibol" class="content-filter__link">
								<small>Deporte</small>Voleibol
							</a>
						</li>
					</ul>
				</div>
			</div>
		</nav>
		<!-- Sports Filter / End -->

		<!-- Content -->
		<div class="site-content">
			<div class="container">

				<?php if (!empty($active_tournaments)): ?>
					<?php foreach ($active_tournaments as $tournament): ?>
						<?php 
						$tournament_standings = getTournamentStandings($conn, $tournament['id_tournament']); 
						if (!empty($tournament_standings)):
						?>
						<!-- Tournament Standings -->
						<div class="card card--has-table mb-4" id="tournament-<?php echo $tournament['id_tournament']; ?>">
							<div class="card__header">
								<h4><?php echo htmlspecialchars($tournament['nombre']); ?> - <?php echo ucfirst(htmlspecialchars($tournament['deporte'])); ?></h4>
								<div class="card__header-meta">
									<span class="badge badge-primary"><?php echo count($tournament_standings); ?> equipos</span>
									<?php if (!empty($tournament['temporada'])): ?>
									<span class="badge badge-secondary ml-2">Temporada <?php echo htmlspecialchars($tournament['temporada']); ?></span>
									<?php endif; ?>
									<button class="btn btn-sm btn-outline-primary ml-2 print-table-btn" onclick="printTable(<?php echo $tournament['id_tournament']; ?>)" title="Imprimir tabla">
										<i class="fas fa-print"></i> Imprimir
									</button>
								</div>
							</div>
							<div class="card__content">
								<div class="table-responsive">
									<table class="table table-hover table-standings table-standings--full">
										<thead>
											<tr>
												<th class="text-center">Pos</th>
												<th class="team-meta">Equipo</th>
												<th class="text-center">PJ</th>
												<th class="text-center">PG</th>
												<th class="text-center">PE</th>
												<th class="text-center">PP</th>
												<th class="text-center">PF</th>
												<th class="text-center">PC</th>
												<th class="text-center">Diff</th>
												<th class="text-center">Pts</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($tournament_standings as $team): ?>
											<tr class="<?php echo $team['destacado'] ? 'highlighted' : ''; ?>">
												<td class="text-center">
													<strong><?php echo $team['posicion']; ?></strong>
												</td>
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
												<td class="text-center"><?php echo $team['partidos_jugados']; ?></td>
												<td class="text-center"><?php echo $team['partidos_ganados']; ?></td>
												<td class="text-center"><?php echo $team['partidos_empatados']; ?></td>
												<td class="text-center"><?php echo $team['partidos_perdidos']; ?></td>
												<td class="text-center"><?php echo $team['puntos_favor']; ?></td>
												<td class="text-center"><?php echo $team['puntos_contra']; ?></td>
												<td class="text-center">
													<?php 
													$diff = $team['puntos_favor'] - $team['puntos_contra'];
													$diff_class = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
													?>
													<span class="<?php echo $diff_class; ?>">
														<?php echo $diff > 0 ? '+' . $diff : $diff; ?>
													</span>
												</td>
												<td class="text-center"><strong class="text-primary"><?php echo $team['puntos_totales']; ?></strong></td>
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<!-- Tournament Standings / End -->
						<?php endif; ?>
					<?php endforeach; ?>
					
					<!-- Legend -->
					<div class="card mt-4">
						<div class="card__header">
							<h5>Leyenda</h5>
						</div>
						<div class="card__content">
							<div class="row">
								<div class="col-md-6">
									<ul class="list-unstyled">
										<li><strong>Pos:</strong> Posición actual</li>
										<li><strong>PJ:</strong> Partidos jugados</li>
										<li><strong>PG:</strong> Partidos ganados</li>
										<li><strong>PE:</strong> Partidos empatados</li>
										<li><strong>PP:</strong> Partidos perdidos</li>
									</ul>
								</div>
								<div class="col-md-6">
									<ul class="list-unstyled">
										<li><strong>PF:</strong> Puntos a favor</li>
										<li><strong>PC:</strong> Puntos en contra</li>
										<li><strong>Diff:</strong> Diferencia de puntos (PF - PC)</li>
										<li><strong>Pts:</strong> Puntos totales del campeonato</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<!-- Legend / End -->
				<?php else: ?>
					<div class="card">
						<div class="card__header">
							<h4><?php echo $selected_sport ? 'Posiciones de ' . ucfirst($selected_sport) : 'Tablas de Posiciones'; ?></h4>
						</div>
						<div class="card__content">
							<?php if ($selected_sport): ?>
								<p class="text-center">No hay torneos activos de <?php echo htmlspecialchars($selected_sport); ?> en este momento.</p>
								<div class="text-center">
									<a href="standings.php" class="btn btn-primary">Ver todas las posiciones</a>
								</div>
							<?php else: ?>
								<p class="text-center">No hay torneos activos en este momento.</p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</div>
		<!-- Content / End -->

		<!-- Sponsors -->
		<div class="sponsors-wrapper">
			<div class="container">
				<div class="sponsors">
					<ul class="sponsors-logos">
						<li class="sponsors__item">
							<a href="#" target="_blank"><img src="assets/images/esports/sponsor-darkgame.png" alt="Sponsor"></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- Sponsors / End -->

		<!-- Footer -->
		<footer class="footer footer--layout-1">
			<div class="footer-widgets">
				<div class="footer-widgets__inner">
					<div class="container">
						<div class="row text-center">
							<div class="col-12">
								<div class="widget widget-about">
									<div class="widget__content">
										<div class="logo">
											<a href="/noticias/"><img src="assets/images/esports/logo.png" alt="AbeJorral Abedeport" width="120px" height="120px"></a>
										</div>
										<p class="text-muted">Club Deportivo AbeJorral Abedeport - Promoviendo el deporte en nuestra comunidad</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="footer-bottom">
				<div class="container">
					<div class="row">
						<div class="col-lg-6">
							<p class="footer-bottom__copyright">&copy; 2025 AbeJorral Abedeport. Todos los derechos reservados.</p>
						</div>
						<div class="col-lg-6">
							<ul class="footer-bottom__nav">
								<li><a href="#">Política de Privacidad</a></li>
								<li><a href="#">Términos de Uso</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- Footer / End -->

	</div>
	<!-- site-wrapper -->

	<!-- JavaScript -->
	<script src="assets/vendor/jquery/jquery.min.js"></script>
	<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/core.js"></script>
	<script src="assets/js/init.js"></script>
	<script src="assets/js/custom.js"></script>
	
	<!-- Show body after page loads -->
	<script>
	$(document).ready(function() {
		$('body').show();
	});
	</script>


</body>
</html>
	<script>
	/**
	 * Print a specific tournament table
	 * @param {number} tournamentId - The ID of the tournament to print
	 */
	function printTable(tournamentId) {
		// Get the tournament card element
		var tournamentCard = document.getElementById('tournament-' + tournamentId);
		
		if (!tournamentCard) {
			alert('No se pudo encontrar la tabla para imprimir');
			return;
		}
		
		// Clone the card to avoid modifying the original
		var printContent = tournamentCard.cloneNode(true);
		
		// Remove the print button and badges from the cloned content
		var printBtn = printContent.querySelector('.print-table-btn');
		if (printBtn) {
			printBtn.remove();
		}
		
		var badges = printContent.querySelectorAll('.badge');
		badges.forEach(function(badge) {
			badge.remove();
		});
		
		// Get the HTML content
		var htmlContent = printContent.innerHTML;
		
		// Create print styles
		var printStyles = `
			<style>
				* { margin: 0; padding: 0; box-sizing: border-box; }
				body { 
					font-family: Arial, sans-serif; 
					padding: 20px;
					color: #000;
				}
				.card { 
					border: none; 
					box-shadow: none; 
					margin-bottom: 20px;
				}
				.card__header h4 { 
					margin-bottom: 15px; 
					color: #000;
					font-size: 18px;
					font-weight: bold;
				}
				.card__content {
					padding: 0;
				}
				.table-responsive {
					overflow: visible;
				}
				.table { 
					border-collapse: collapse; 
					width: 100%; 
					margin-top: 10px;
					font-size: 11px;
				}
				.table th, .table td { 
					border: 1px solid #333; 
					padding: 6px 4px;
					text-align: center;
					color: #000;
				}
				.table thead th { 
					background-color: #e0e0e0;
					font-weight: bold;
				}
				.table tbody tr:nth-child(even) {
					background-color: #f5f5f5;
				}
				.team-meta { 
					text-align: left !important;
					display: flex;
					align-items: center;
					gap: 8px;
				}
				.team-meta__logo { 
					margin: 0;
					flex-shrink: 0;
				}
				.team-meta__logo img { 
					max-width: 25px; 
					max-height: 25px;
					display: block;
				}
				.team-meta__info {
					flex-grow: 1;
				}
				.team-meta__name { 
					margin: 0;
					font-size: 11px;
					font-weight: bold;
					color: #000;
				}
				.team-meta__place { 
					font-size: 9px;
					color: #666;
					display: block;
				}
				.highlighted {
					background-color: #fff3cd !important;
				}
				.text-success { color: #28a745 !important; }
				.text-danger { color: #dc3545 !important; }
				.text-muted { color: #6c757d !important; }
				.text-primary { color: #007bff !important; font-weight: bold; }
				@media print {
					body { padding: 10px; }
				}
			</style>
		`;
		
		// Create the full HTML document
		var printDocument = `
			<!DOCTYPE html>
			<html>
			<head>
				<meta charset="UTF-8">
				<title>Tabla de Posiciones</title>
				${printStyles}
			</head>
			<body>
				${htmlContent}
			</body>
			</html>
		`;
		
		// Open a new window
		var printWindow = window.open('', '_blank', 'width=800,height=600');
		
		if (!printWindow) {
			alert('Por favor, permita las ventanas emergentes para imprimir');
			return;
		}
		
		// Write the document
		printWindow.document.open();
		printWindow.document.write(printDocument);
		printWindow.document.close();
		
		// Wait for images to load, then print
		printWindow.onload = function() {
			setTimeout(function() {
				printWindow.focus();
				printWindow.print();
				// Close after printing or canceling
				setTimeout(function() {
					printWindow.close();
				}, 100);
			}, 500);
		};
	}
	</script>

</body>
</html>
