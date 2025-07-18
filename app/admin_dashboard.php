<?php
include "blog_functions.php";

// Get statistics
$total_posts = getPostCountByStatus();
$published_posts = getPostCountByStatus('publicado');
$draft_posts = getPostCountByStatus('borrador');
$archived_posts = getPostCountByStatus('archivado');

// Get recent posts
$recent_posts = getAllBlogPosts(5);
?>

<div class="row">
    <div class="col-12">
        <h1 class="page-title mb-4">
            <i class="bi bi-speedometer2 me-2"></i>Panel de Administración
        </h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $total_posts; ?></h4>
                        <p class="card-text mb-0">Total de Posts</p>
                    </div>
                    <i class="bi bi-file-text fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $published_posts; ?></h4>
                        <p class="card-text mb-0">Publicados</p>
                    </div>
                    <i class="bi bi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $draft_posts; ?></h4>
                        <p class="card-text mb-0">Borradores</p>
                    </div>
                    <i class="bi bi-pencil-square fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0"><?php echo $archived_posts; ?></h4>
                        <p class="card-text mb-0">Archivados</p>
                    </div>
                    <i class="bi bi-archive fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="?page=create_post" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo Post
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="?page=manage_posts" class="btn btn-outline-primary w-100">
                            <i class="bi bi-list-ul me-2"></i>Gestionar Posts
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="?page=blog_preview" class="btn btn-outline-success w-100">
                            <i class="bi bi-eye me-2"></i>Vista Previa
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="?page=blog_viewer" class="btn btn-outline-info w-100" target="_blank">
                            <i class="bi bi-newspaper me-2"></i>Ver Blog Público
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="?page=blog_settings" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-gear me-2"></i>Configuración
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Posts -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Posts Recientes
                </h5>
                <a href="?page=manage_posts" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_posts)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hay posts creados aún</p>
                        <a href="?page=create_post" class="btn btn-primary">Crear Primer Post</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Autor</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_posts as $post): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($post['titulo']); ?></strong>
                                            <?php if ($post['imagen']): ?>
                                                <i class="bi bi-image text-muted ms-1" title="Tiene imagen"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['autor_nombre'] . ' ' . $post['autor_apellido']); ?></td>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'borrador' => 'warning',
                                                'publicado' => 'success',
                                                'archivado' => 'secondary'
                                            ];
                                            $status_text = [
                                                'borrador' => 'Borrador',
                                                'publicado' => 'Publicado',
                                                'archivado' => 'Archivado'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_badges[$post['estado']]; ?>">
                                                <?php echo $status_text[$post['estado']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $date = $post['estado'] == 'publicado' ? $post['fecha_publicacion'] : $post['fecha_creacion'];
                                            echo date('d/m/Y H:i', strtotime($date));
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="?page=edit_post&id=<?php echo $post['id_post']; ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?page=view_post&id=<?php echo $post['id_post']; ?>" 
                                                   class="btn btn-outline-info" title="Ver">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deletePost(<?php echo $post['id_post']; ?>)" title="Eliminar">
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

<script>
function deletePost(postId) {
    if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
        window.location.href = '?page=delete_post&id=' + postId;
    }
}
</script> 