<?php
include "blog_functions.php";

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected_posts'])) {
    $action = $_POST['bulk_action'];
    $selected_posts = $_POST['selected_posts'];
    
    if (!empty($selected_posts)) {
        foreach ($selected_posts as $post_id) {
            switch ($action) {
                case 'publish':
                    updateBlogPost($post_id, ['estado' => 'publicado']);
                    break;
                case 'draft':
                    updateBlogPost($post_id, ['estado' => 'borrador']);
                    break;
                case 'archive':
                    updateBlogPost($post_id, ['estado' => 'archivado']);
                    break;
                case 'delete':
                    deleteBlogPost($post_id);
                    break;
            }
        }
        $message = 'Acción aplicada exitosamente.';
        $message_type = 'success';
    }
}

// Handle individual delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (deleteBlogPost($_GET['delete'])) {
        $message = 'Post eliminado exitosamente.';
        $message_type = 'success';
    } else {
        $message = 'Error al eliminar el post.';
        $message_type = 'danger';
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['p'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get posts with filters
$posts = getAllBlogPosts($per_page, $offset);
$total_posts = getPostCountByStatus();

// Calculate pagination
$total_pages = ceil($total_posts / $per_page);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title mb-0">
                <i class="bi bi-list-ul me-2"></i>Gestionar Posts
            </h1>
            <a href="?page=create_post" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Post
            </a>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Filters and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="page" value="manage_posts">
                    
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Buscar por título...">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="borrador" <?php echo $status_filter == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                            <option value="publicado" <?php echo $status_filter == 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                            <option value="archivado" <?php echo $status_filter == 'archivado' ? 'selected' : ''; ?>>Archivado</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Filtrar
                            </button>
                            <a href="?page=manage_posts" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Posts Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-table me-2"></i>Lista de Posts
                    </h5>
                    <span class="text-muted"><?php echo $total_posts; ?> posts total</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($posts)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No se encontraron posts</p>
                        <a href="?page=create_post" class="btn btn-primary">Crear Primer Post</a>
                    </div>
                <?php else: ?>
                    <form method="POST" id="bulk-form">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Título</th>
                                        <th>Autor</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_posts[]" 
                                                       value="<?php echo $post['id_post']; ?>" 
                                                       class="form-check-input post-checkbox">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($post['imagen']): ?>
                                                        <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" 
                                                             alt="Imagen" class="rounded me-2" 
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($post['titulo']); ?></strong>
                                                        <?php if ($post['etiquetas']): ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($post['etiquetas']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
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
                        
                        <!-- Bulk Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" style="width: auto;" name="bulk_action">
                                    <option value="">Acciones en lote</option>
                                    <option value="publish">Publicar</option>
                                    <option value="draft">Marcar como borrador</option>
                                    <option value="archive">Archivar</option>
                                    <option value="delete">Eliminar</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary" 
                                        onclick="return confirm('¿Estás seguro de aplicar esta acción?')">
                                    Aplicar
                                </button>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=manage_posts&p=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                    Anterior
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=manage_posts&p=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=manage_posts&p=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">
                                                    Siguiente
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.post-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Update select all when individual checkboxes change
document.querySelectorAll('.post-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.post-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.post-checkbox:checked');
        document.getElementById('select-all').checked = allCheckboxes.length === checkedCheckboxes.length;
    });
});

function deletePost(postId) {
    if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
        window.location.href = '?page=manage_posts&delete=' + postId;
    }
}
</script> 