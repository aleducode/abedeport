<?php
include "conn.php";

// Require admin authentication and authorization
$data = requireAdmin();

// Function to get all tournaments
function getAllTournaments($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT t.*, COUNT(et.id_equipo_tournament) as team_count 
            FROM tournaments t 
            LEFT JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
            GROUP BY t.id_tournament 
            ORDER BY t.fecha_inicio DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Function to get tournament by ID
function getTournamentById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM tournaments WHERE id_tournament = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return null;
    }
}

// Function to get teams in tournament
function getTournamentTeams($conn, $tournament_id) {
    try {
        $stmt = $conn->prepare("
            SELECT * FROM equipos_tournament 
            WHERE id_tournament = ? 
            ORDER BY posicion ASC, puntos_totales DESC
        ");
        $stmt->bindParam(1, $tournament_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Function to create tournament
function createTournament($conn, $data) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO tournaments (nombre, deporte, temporada, estado, fecha_inicio, fecha_fin)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['deporte'],
            $data['temporada'],
            $data['estado'],
            $data['fecha_inicio'],
            $data['fecha_fin']
        ]);
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to update tournament
function updateTournament($conn, $id, $data) {
    try {
        $stmt = $conn->prepare("
            UPDATE tournaments 
            SET nombre = ?, deporte = ?, temporada = ?, estado = ?, fecha_inicio = ?, fecha_fin = ?
            WHERE id_tournament = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['deporte'],
            $data['temporada'],
            $data['estado'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $id
        ]);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to add/update team in tournament
function updateTeamInTournament($conn, $tournament_id, $team_data) {
    try {
        // Check if team exists
        $stmt = $conn->prepare("
            SELECT id_equipo_tournament FROM equipos_tournament 
            WHERE id_tournament = ? AND nombre_equipo = ?
        ");
        $stmt->execute([$tournament_id, $team_data['nombre_equipo']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing team
            $stmt = $conn->prepare("
                UPDATE equipos_tournament SET
                ciudad = ?, pais = ?, logo = ?, partidos_jugados = ?, 
                partidos_ganados = ?, partidos_perdidos = ?, partidos_empatados = ?,
                puntos_favor = ?, puntos_contra = ?, puntos_totales = ?, posicion = ?, destacado = ?
                WHERE id_equipo_tournament = ?
            ");
            return $stmt->execute([
                $team_data['ciudad'], $team_data['pais'], $team_data['logo'],
                $team_data['partidos_jugados'], $team_data['partidos_ganados'],
                $team_data['partidos_perdidos'], $team_data['partidos_empatados'],
                $team_data['puntos_favor'], $team_data['puntos_contra'],
                $team_data['puntos_totales'], $team_data['posicion'],
                $team_data['destacado'], $existing['id_equipo_tournament']
            ]);
        } else {
            // Insert new team
            $stmt = $conn->prepare("
                INSERT INTO equipos_tournament 
                (id_tournament, nombre_equipo, ciudad, pais, logo, partidos_jugados,
                partidos_ganados, partidos_perdidos, partidos_empatados, puntos_favor,
                puntos_contra, puntos_totales, posicion, destacado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $tournament_id, $team_data['nombre_equipo'], $team_data['ciudad'],
                $team_data['pais'], $team_data['logo'], $team_data['partidos_jugados'],
                $team_data['partidos_ganados'], $team_data['partidos_perdidos'],
                $team_data['partidos_empatados'], $team_data['puntos_favor'],
                $team_data['puntos_contra'], $team_data['puntos_totales'],
                $team_data['posicion'], $team_data['destacado']
            ]);
        }
    } catch(PDOException $e) {
        return false;
    }
}

// Function to delete team from tournament
function deleteTeamFromTournament($conn, $team_id) {
    try {
        $stmt = $conn->prepare("DELETE FROM equipos_tournament WHERE id_equipo_tournament = ?");
        return $stmt->execute([$team_id]);
    } catch(PDOException $e) {
        return false;
    }
}

// Function to delete tournament
function deleteTournament($conn, $tournament_id) {
    try {
        $conn->beginTransaction();
        
        // Delete all teams first
        $stmt = $conn->prepare("DELETE FROM equipos_tournament WHERE id_tournament = ?");
        $stmt->execute([$tournament_id]);
        
        // Delete tournament
        $stmt = $conn->prepare("DELETE FROM tournaments WHERE id_tournament = ?");
        $stmt->execute([$tournament_id]);
        
        $conn->commit();
        return true;
    } catch(PDOException $e) {
        $conn->rollback();
        return false;
    }
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_tournament':
                $result = createTournament($conn, $_POST);
                if ($result) {
                    $message = 'Torneo creado exitosamente';
                    $messageType = 'success';
                } else {
                    $message = 'Error al crear el torneo';
                    $messageType = 'danger';
                }
                break;
                
            case 'update_tournament':
                $result = updateTournament($conn, $_POST['tournament_id'], $_POST);
                if ($result) {
                    $message = 'Torneo actualizado exitosamente';
                    $messageType = 'success';
                } else {
                    $message = 'Error al actualizar el torneo';
                    $messageType = 'danger';
                }
                break;
                
            case 'delete_tournament':
                $result = deleteTournament($conn, $_POST['tournament_id']);
                if ($result) {
                    $message = 'Torneo eliminado exitosamente';
                    $messageType = 'success';
                } else {
                    $message = 'Error al eliminar el torneo';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get all tournaments for display
$tournaments = getAllTournaments($conn);

// Sports options
$sports = [
    'futbol' => 'Fútbol',
    'futsal' => 'Futsal', 
    'baloncesto' => 'Baloncesto',
    'voleibol' => 'Voleibol'
];

$estados = [
    'activo' => 'Activo',
    'finalizado' => 'Finalizado',
    'proximo' => 'Próximo'
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">
                    <i class="bi bi-trophy-fill me-2"></i>Gestión de Torneos
                </h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tournamentModal">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Torneo
                </button>
            </div>
        </div>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Tournaments List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul me-2"></i>Lista de Torneos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tournaments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-trophy text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No hay torneos registrados</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Deporte</th>
                                    <th>Temporada</th>
                                    <th>Estado</th>
                                    <th>Equipos</th>
                                    <th>Fechas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tournaments as $tournament): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tournament['nombre']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo $sports[$tournament['deporte']]; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($tournament['temporada']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $tournament['estado'] === 'activo' ? 'success' : 
                                                ($tournament['estado'] === 'finalizado' ? 'secondary' : 'warning'); 
                                        ?>">
                                            <?php echo $estados[$tournament['estado']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $tournament['team_count']; ?> equipos
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php 
                                            if ($tournament['fecha_inicio']) {
                                                echo date('d/m/Y', strtotime($tournament['fecha_inicio']));
                                            }
                                            if ($tournament['fecha_fin']) {
                                                echo ' - ' . date('d/m/Y', strtotime($tournament['fecha_fin']));
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?page=tournament_standings&id=<?php echo $tournament['id_tournament']; ?>" 
                                               class="btn btn-outline-primary" title="Gestionar Tabla">
                                                <i class="bi bi-table"></i>
                                            </a>
                                            <button class="btn btn-outline-warning edit-tournament" 
                                                    data-tournament='<?php echo json_encode($tournament); ?>' 
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger delete-tournament" 
                                                    data-id="<?php echo $tournament['id_tournament']; ?>" 
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

<!-- Tournament Modal -->
<div class="modal fade" id="tournamentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-trophy-fill me-2"></i>
                    <span id="modalTitle">Nuevo Torneo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tournamentForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="create_tournament">
                    <input type="hidden" name="tournament_id" id="tournamentId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre del Torneo</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deporte" class="form-label">Deporte</label>
                            <select class="form-select" name="deporte" id="deporte" required>
                                <option value="">Seleccionar deporte</option>
                                <?php foreach ($sports as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="temporada" class="form-label">Temporada</label>
                            <input type="text" class="form-control" name="temporada" id="temporada" 
                                   placeholder="Ej: 2024-1, 2024" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado" required>
                                <?php foreach ($estados as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este torneo?</p>
                <p class="text-danger"><strong>Esta acción eliminará también todos los equipos y estadísticas del torneo.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_tournament">
                    <input type="hidden" name="tournament_id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit tournament
    document.querySelectorAll('.edit-tournament').forEach(button => {
        button.addEventListener('click', function() {
            const tournament = JSON.parse(this.dataset.tournament);
            
            document.getElementById('modalTitle').textContent = 'Editar Torneo';
            document.getElementById('formAction').value = 'update_tournament';
            document.getElementById('tournamentId').value = tournament.id_tournament;
            document.getElementById('nombre').value = tournament.nombre;
            document.getElementById('deporte').value = tournament.deporte;
            document.getElementById('temporada').value = tournament.temporada;
            document.getElementById('estado').value = tournament.estado;
            document.getElementById('fecha_inicio').value = tournament.fecha_inicio;
            document.getElementById('fecha_fin').value = tournament.fecha_fin;
            
            new bootstrap.Modal(document.getElementById('tournamentModal')).show();
        });
    });
    
    // Delete tournament
    document.querySelectorAll('.delete-tournament').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteId').value = this.dataset.id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
    
    // Reset form when modal closes
    document.getElementById('tournamentModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('modalTitle').textContent = 'Nuevo Torneo';
        document.getElementById('formAction').value = 'create_tournament';
        document.getElementById('tournamentForm').reset();
        document.getElementById('tournamentId').value = '';
    });
});
</script>