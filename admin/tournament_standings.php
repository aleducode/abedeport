<?php
require_once "conn.php";
include "tournament_management.php";

// Get tournament ID from URL
$tournament_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tournament_id) {
    header('Location: ?page=tournament_management');
    exit;
}

// Get tournament info
$tournament = getTournamentById($conn, $tournament_id);
if (!$tournament) {
    header('Location: ?page=tournament_management');
    exit;
}

// Get tournament teams
$teams = getTournamentTeams($conn, $tournament_id);

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_team':
                $team_data = [
                    'nombre_equipo' => $_POST['nombre_equipo'],
                    'ciudad' => $_POST['ciudad'] ?? '',
                    'pais' => $_POST['pais'] ?? '',
                    'logo' => $_POST['logo'] ?? '',
                    'partidos_jugados' => (int)($_POST['partidos_jugados'] ?? 0),
                    'partidos_ganados' => (int)($_POST['partidos_ganados'] ?? 0),
                    'partidos_perdidos' => (int)($_POST['partidos_perdidos'] ?? 0),
                    'partidos_empatados' => (int)($_POST['partidos_empatados'] ?? 0),
                    'puntos_favor' => (int)($_POST['puntos_favor'] ?? 0),
                    'puntos_contra' => (int)($_POST['puntos_contra'] ?? 0),
                    'puntos_totales' => (int)($_POST['puntos_totales'] ?? 0),
                    'posicion' => (int)($_POST['posicion'] ?? 0),
                    'destacado' => isset($_POST['destacado']) ? 1 : 0
                ];
                
                $result = updateTeamInTournament($conn, $tournament_id, $team_data);
                if ($result) {
                    $message = 'Equipo agregado/actualizado exitosamente';
                    $messageType = 'success';
                } else {
                    $message = 'Error al agregar/actualizar el equipo';
                    $messageType = 'danger';
                }
                break;
                
            case 'delete_team':
                $result = deleteTeamFromTournament($conn, $_POST['team_id']);
                if ($result) {
                    $message = 'Equipo eliminado exitosamente';
                    $messageType = 'success';
                } else {
                    $message = 'Error al eliminar el equipo';
                    $messageType = 'danger';
                }
                break;
                
            case 'upload_csv':
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
                    if ($handle !== FALSE) {
                        $header = fgetcsv($handle); // Skip header row
                        $added_teams = 0;
                        $errors = 0;
                        
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            if (count($data) >= 3) { // At least team name, points, position
                                $team_data = [
                                    'nombre_equipo' => $data[0] ?? '',
                                    'ciudad' => $data[1] ?? '',
                                    'pais' => $data[2] ?? '',
                                    'logo' => $data[3] ?? '',
                                    'partidos_jugados' => (int)($data[4] ?? 0),
                                    'partidos_ganados' => (int)($data[5] ?? 0),
                                    'partidos_perdidos' => (int)($data[6] ?? 0),
                                    'partidos_empatados' => (int)($data[7] ?? 0),
                                    'puntos_favor' => (int)($data[8] ?? 0),
                                    'puntos_contra' => (int)($data[9] ?? 0),
                                    'puntos_totales' => (int)($data[10] ?? 0),
                                    'posicion' => (int)($data[11] ?? 0),
                                    'destacado' => isset($data[12]) && $data[12] == '1' ? 1 : 0
                                ];
                                
                                if (updateTeamInTournament($conn, $tournament_id, $team_data)) {
                                    $added_teams++;
                                } else {
                                    $errors++;
                                }
                            }
                        }
                        fclose($handle);
                        
                        if ($added_teams > 0) {
                            $message = "Se procesaron $added_teams equipos exitosamente";
                            if ($errors > 0) {
                                $message .= " (con $errors errores)";
                            }
                            $messageType = 'success';
                        } else {
                            $message = 'No se pudieron procesar los equipos';
                            $messageType = 'danger';
                        }
                    }
                } else {
                    $message = 'Error al subir el archivo CSV';
                    $messageType = 'danger';
                }
                break;
        }
        
        // Refresh teams data after any operation
        $teams = getTournamentTeams($conn, $tournament_id);
    }
}

// Sports info
$sports = [
    'futbol' => ['name' => 'Fútbol', 'icon' => 'bi-dribbble'],
    'futsal' => ['name' => 'Futsal', 'icon' => 'bi-circle'],
    'baloncesto' => ['name' => 'Baloncesto', 'icon' => 'bi-circle-fill'],
    'voleibol' => ['name' => 'Voleibol', 'icon' => 'bi-circle-half']
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="?page=tournament_management">Torneos</a>
                    </li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($tournament['nombre']); ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title mb-1">
                        <i class="<?php echo $sports[$tournament['deporte']]['icon']; ?> me-2"></i>
                        <?php echo htmlspecialchars($tournament['nombre']); ?>
                    </h1>
                    <div class="text-muted">
                        <span class="badge bg-primary me-2"><?php echo $sports[$tournament['deporte']]['name']; ?></span>
                        <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($tournament['temporada']); ?></span>
                        <span class="badge bg-<?php 
                            echo $tournament['estado'] === 'activo' ? 'success' : 
                                ($tournament['estado'] === 'finalizado' ? 'secondary' : 'warning'); 
                        ?>"><?php echo ucfirst($tournament['estado']); ?></span>
                    </div>
                </div>
                <div>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#csvModal">
                        <i class="bi bi-upload me-1"></i>Subir CSV
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamModal">
                        <i class="bi bi-plus-circle me-1"></i>Agregar Equipo
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Tournament Standings -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-table me-2"></i>Tabla de Posiciones - Partidos y Puntos
                        <span class="badge bg-info ms-2"><?php echo count($teams); ?> equipos</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($teams)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No hay equipos en este torneo</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamModal">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Primer Equipo
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Pos</th>
                                    <th>Equipo</th>
                                    <th class="text-center">Partidos</th>
                                    <th class="text-center">Puntos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams as $team): ?>
                                <tr class="<?php echo $team['destacado'] ? 'table-warning' : ''; ?>">
                                    <td>
                                        <strong class="<?php echo $team['posicion'] <= 3 ? 'text-primary' : ''; ?>">
                                            <?php echo $team['posicion']; ?>
                                        </strong>
                                        <?php if ($team['destacado']): ?>
                                        <i class="bi bi-star-fill text-warning ms-1" title="Destacado"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($team['logo']): ?>
                                            <img src="<?php echo htmlspecialchars($team['logo']); ?>" 
                                                 alt="Logo" class="me-2" style="width: 24px; height: 24px; object-fit: contain;">
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($team['nombre_equipo']); ?></strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <strong><?php echo $team['partidos_jugados']; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary"><?php echo $team['puntos_totales']; ?></strong>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning edit-team" 
                                                    data-team='<?php echo json_encode($team); ?>' 
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger delete-team" 
                                                    data-id="<?php echo $team['id_equipo_tournament']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($team['nombre_equipo']); ?>"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team Modal -->
<div class="modal fade" id="teamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-people-fill me-2"></i>
                    <span id="teamModalTitle">Agregar Equipo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="teamForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_team">
                    <input type="hidden" name="team_id" id="teamId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_equipo" class="form-label">Nombre del Equipo*</label>
                            <input type="text" class="form-control" name="nombre_equipo" id="nombre_equipo" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ciudad" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="ciudad" id="ciudad">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="pais" class="form-label">País</label>
                            <input type="text" class="form-control" name="pais" id="pais">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="logo" class="form-label">URL del Logo</label>
                            <input type="url" class="form-control" name="logo" id="logo" 
                                   placeholder="https://ejemplo.com/logo.png">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="posicion" class="form-label">Posición*</label>
                            <input type="number" class="form-control" name="posicion" id="posicion" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="destacado" id="destacado">
                                <label class="form-check-label" for="destacado">
                                    Destacado
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="partidos_jugados" class="form-label">Partidos Jugados</label>
                            <input type="number" class="form-control" name="partidos_jugados" id="partidos_jugados" min="0" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="partidos_ganados" class="form-label">Partidos Ganados</label>
                            <input type="number" class="form-control" name="partidos_ganados" id="partidos_ganados" min="0" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="partidos_empatados" class="form-label">Partidos Empatados</label>
                            <input type="number" class="form-control" name="partidos_empatados" id="partidos_empatados" min="0" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="partidos_perdidos" class="form-label">Partidos Perdidos</label>
                            <input type="number" class="form-control" name="partidos_perdidos" id="partidos_perdidos" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="puntos_favor" class="form-label">Puntos a Favor</label>
                            <input type="number" class="form-control" name="puntos_favor" id="puntos_favor" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="puntos_contra" class="form-label">Puntos en Contra</label>
                            <input type="number" class="form-control" name="puntos_contra" id="puntos_contra" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="puntos_totales" class="form-label">Puntos Totales</label>
                            <input type="number" class="form-control" name="puntos_totales" id="puntos_totales" min="0" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSV Upload Modal -->
<div class="modal fade" id="csvModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Subir Tabla desde CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_csv">
                    
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Archivo CSV</label>
                        <input type="file" class="form-control" name="csv_file" id="csv_file" 
                               accept=".csv" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6>Formato esperado del CSV:</h6>
                        <small>
                            <strong>Columnas:</strong> Equipo, Ciudad, País, Logo, PJ, PG, PP, PE, PF, PC, Pts, Posición, Destacado<br>
                            <strong>Ejemplo:</strong><br>
                            <code>Barcelona,Barcelona,España,,10,8,1,1,25,8,25,1,0</code>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i>Subir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Team Modal -->
<div class="modal fade" id="deleteTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el equipo <strong id="teamNameToDelete"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_team">
                    <input type="hidden" name="team_id" id="deleteTeamId">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit team
    document.querySelectorAll('.edit-team').forEach(button => {
        button.addEventListener('click', function() {
            const team = JSON.parse(this.dataset.team);
            
            document.getElementById('teamModalTitle').textContent = 'Editar Equipo';
            document.getElementById('teamId').value = team.id_equipo_tournament;
            document.getElementById('nombre_equipo').value = team.nombre_equipo;
            document.getElementById('ciudad').value = team.ciudad || '';
            document.getElementById('pais').value = team.pais || '';
            document.getElementById('logo').value = team.logo || '';
            document.getElementById('partidos_jugados').value = team.partidos_jugados;
            document.getElementById('partidos_ganados').value = team.partidos_ganados;
            document.getElementById('partidos_perdidos').value = team.partidos_perdidos;
            document.getElementById('partidos_empatados').value = team.partidos_empatados;
            document.getElementById('puntos_favor').value = team.puntos_favor;
            document.getElementById('puntos_contra').value = team.puntos_contra;
            document.getElementById('puntos_totales').value = team.puntos_totales;
            document.getElementById('posicion').value = team.posicion;
            document.getElementById('destacado').checked = team.destacado == 1;
            
            new bootstrap.Modal(document.getElementById('teamModal')).show();
        });
    });
    
    // Delete team
    document.querySelectorAll('.delete-team').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteTeamId').value = this.dataset.id;
            document.getElementById('teamNameToDelete').textContent = this.dataset.name;
            new bootstrap.Modal(document.getElementById('deleteTeamModal')).show();
        });
    });
    
    // Reset team form when modal closes
    document.getElementById('teamModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('teamModalTitle').textContent = 'Agregar Equipo';
        document.getElementById('teamForm').reset();
        document.getElementById('teamId').value = '';
    });
});
</script>