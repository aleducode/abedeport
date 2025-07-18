<?php
include "blog_functions.php";

$redirect_url = '';

// Get post ID
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$post_id) {
    $redirect_url = '?page=manage_posts';
}

// Get post data
$post = getBlogPostById($post_id);
if (!$post) {
    $redirect_url = '?page=manage_posts';
}

// Handle status change
if (isset($_GET['change_status'])) {
    $new_status = $_GET['change_status'];
    if (in_array($new_status, ['borrador', 'publicado', 'archivado'])) {
        updateBlogPost($post_id, ['estado' => $new_status]);
        $post = getBlogPostById($post_id); // Refresh data
        $message = 'Estado actualizado exitosamente.';
        $message_type = 'success';
    }
}

// Handle redirects with JavaScript
if ($redirect_url) {
    echo '<script>window.location.href = "' . htmlspecialchars($redirect_url) . '";</script>';
    exit;
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title mb-0">
                <i class="bi bi-eye me-2"></i>Ver Post
            </h1>
            <div class="d-flex gap-2">
                <a href="?page=edit_post&id=<?php echo $post_id; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i>Editar
                </a>
                <a href="?page=manage_posts" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Post Content -->
        <div class="card">
            <div class="card-body">
                <!-- Post Header -->
                <div class="mb-4">
                    <h1 class="display-5 fw-bold text-primary mb-3">
                        <?php echo htmlspecialchars($post['titulo']); ?>
                    </h1>
                    
                    <div class="d-flex flex-wrap gap-3 align-items-center text-muted mb-3">
                        <div>
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($post['autor_nombre'] . ' ' . $post['autor_apellido']); ?>
                        </div>
                        <div>
                            <i class="bi bi-calendar3 me-1"></i>
                            <?php 
                            $date = $post['estado'] == 'publicado' ? $post['fecha_publicacion'] : $post['fecha_creacion'];
                            echo date('d/m/Y H:i', strtotime($date));
                            ?>
                        </div>
                        <div>
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
                        </div>
                    </div>
                    
                    <?php if ($post['etiquetas']): ?>
                        <div class="mb-3">
                            <?php
                            $tags = explode(',', $post['etiquetas']);
                            foreach ($tags as $tag): ?>
                                <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Featured Image -->
                <?php if ($post['imagen']): ?>
                    <div class="mb-4">
                        <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($post['titulo']); ?>" 
                             class="img-fluid rounded" style="max-height: 400px; width: 100%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                
                <!-- Post Content -->
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['contenido'])); ?>
                </div>
                
                <!-- Post Footer -->
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="bi bi-clock me-1"></i>
                            Última actualización: <?php echo date('d/m/Y H:i', strtotime($post['fecha_actualizacion'])); ?>
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyUrl()">
                            <i class="bi bi-link-45deg me-1"></i>Copiar URL
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printPost()">
                            <i class="bi bi-printer me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Post Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Información del Post
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Detalles:</h6>
                    <ul class="list-unstyled">
                        <li><strong>ID:</strong> <?php echo $post['id_post']; ?></li>
                        <li><strong>Slug:</strong> <code><?php echo htmlspecialchars($post['slug']); ?></code></li>
                        <li><strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($post['fecha_creacion'])); ?></li>
                        <?php if ($post['fecha_publicacion']): ?>
                            <li><strong>Publicado:</strong> <?php echo date('d/m/Y H:i', strtotime($post['fecha_publicacion'])); ?></li>
                        <?php endif; ?>
                        <li><strong>Actualizado:</strong> <?php echo date('d/m/Y H:i', strtotime($post['fecha_actualizacion'])); ?></li>
                    </ul>
                </div>
                
                <?php if ($post['meta_descripcion']): ?>
                    <div class="mb-3">
                        <h6>Descripción Meta:</h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($post['meta_descripcion']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?page=edit_post&id=<?php echo $post_id; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Editar Post
                    </a>
                    
                    <!-- Status Change Buttons -->
                    <?php if ($post['estado'] != 'publicado'): ?>
                        <a href="?page=view_post&id=<?php echo $post_id; ?>&change_status=publicado" 
                           class="btn btn-success" 
                           onclick="return confirm('¿Publicar este post?')">
                            <i class="bi bi-check-circle me-2"></i>Publicar
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($post['estado'] != 'borrador'): ?>
                        <a href="?page=view_post&id=<?php echo $post_id; ?>&change_status=borrador" 
                           class="btn btn-warning" 
                           onclick="return confirm('¿Marcar como borrador?')">
                            <i class="bi bi-pencil-square me-2"></i>Marcar como Borrador
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($post['estado'] != 'archivado'): ?>
                        <a href="?page=view_post&id=<?php echo $post_id; ?>&change_status=archivado" 
                           class="btn btn-secondary" 
                           onclick="return confirm('¿Archivar este post?')">
                            <i class="bi bi-archive me-2"></i>Archivar
                        </a>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-outline-danger" onclick="deletePost(<?php echo $post_id; ?>)">
                        <i class="bi bi-trash me-2"></i>Eliminar Post
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Preview Link -->
        <?php if ($post['estado'] == 'publicado'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye me-2"></i>Vista Pública
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Este post está publicado y es visible públicamente.
                    </p>
                    <a href="../blog/<?php echo htmlspecialchars($post['slug']); ?>" 
                       target="_blank" class="btn btn-outline-primary w-100">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Ver en el Blog
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye-slash me-2"></i>Vista Pública
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Este post no está publicado y no es visible públicamente.
                    </p>
                    <button type="button" class="btn btn-outline-secondary w-100" disabled>
                        <i class="bi bi-lock me-2"></i>No Disponible
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.post-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.post-content p {
    margin-bottom: 1.5rem;
}

.post-content h1, .post-content h2, .post-content h3, 
.post-content h4, .post-content h5, .post-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.post-content ul, .post-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.post-content blockquote {
    border-left: 4px solid var(--primary-color);
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.post-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

.post-content pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1rem 0;
}

.post-content pre code {
    background: none;
    padding: 0;
}
</style>

<script>
function copyUrl() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        alert('URL copiada al portapapeles');
    }, function(err) {
        console.error('Error al copiar URL: ', err);
        alert('Error al copiar URL');
    });
}

function printPost() {
    window.print();
}

function deletePost(postId) {
    if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
        window.location.href = '?page=manage_posts&delete=' + postId;
    }
}
</script> 