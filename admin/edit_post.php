<?php
include "blog_functions.php";

$message = '';
$message_type = '';
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $estado = $_POST['estado'] ?? 'borrador';
    $meta_descripcion = trim($_POST['meta_descripcion'] ?? '');
    $etiquetas = trim($_POST['etiquetas'] ?? '');
    
    // Validation
    if (empty($titulo)) {
        $message = 'El título es obligatorio.';
        $message_type = 'danger';
    } elseif (empty($contenido)) {
        $message = 'El contenido es obligatorio.';
        $message_type = 'danger';
    } else {
        $imagen_path = $post['imagen']; // Keep existing image by default
        
        // Handle new image upload
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $upload_result = uploadImage($_FILES['imagen']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $message_type = 'danger';
            } else {
                // Delete old image if exists
                if ($post['imagen']) {
                    $old_image_path = "../" . $post['imagen'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $imagen_path = $upload_result['success'];
            }
        }
        
        if ($message_type != 'danger') {
            $post_data = [
                'titulo' => $titulo,
                'contenido' => $contenido,
                'imagen' => $imagen_path,
                'estado' => $estado,
                'meta_descripcion' => $meta_descripcion,
                'etiquetas' => $etiquetas
            ];
            
            if (updateBlogPost($post_id, $post_data)) {
                $message = 'Post actualizado exitosamente.';
                $message_type = 'success';
                
                // Refresh post data
                $post = getBlogPostById($post_id);
                
                // Set redirect URL if published
                if ($estado == 'publicado') {
                    $redirect_url = '?page=view_post&id=' . $post_id;
                }
            } else {
                $message = 'Error al actualizar el post.';
                $message_type = 'danger';
            }
        }
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
                <i class="bi bi-pencil-square me-2"></i>Editar Post
            </h1>
            <div class="d-flex gap-2">
                <a href="?page=view_post&id=<?php echo $post_id; ?>" class="btn btn-outline-info">
                    <i class="bi bi-eye me-2"></i>Ver Post
                </a>
                <a href="?page=manage_posts" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Editar Contenido
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($post['titulo']); ?>" 
                               required maxlength="255">
                        <div class="form-text">Máximo 255 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido *</label>
                        <textarea class="form-control" id="contenido" name="contenido" rows="15" 
                                  required><?php echo htmlspecialchars($post['contenido']); ?></textarea>
                        <div class="form-text">
                            Puedes usar HTML básico para formatear el contenido.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen Destacada</label>
                        <?php if ($post['imagen']): ?>
                            <div class="mb-2">
                                <img src="../<?php echo htmlspecialchars($post['imagen']); ?>" 
                                     alt="Imagen actual" class="img-thumbnail" style="max-height: 200px;">
                                <div class="form-text">Imagen actual</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="imagen" name="imagen" 
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <div class="form-text">
                            Deja vacío para mantener la imagen actual. Formatos: JPG, PNG, GIF. Máximo 5MB.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="borrador" <?php echo $post['estado'] == 'borrador' ? 'selected' : ''; ?>>
                                        Borrador
                                    </option>
                                    <option value="publicado" <?php echo $post['estado'] == 'publicado' ? 'selected' : ''; ?>>
                                        Publicado
                                    </option>
                                    <option value="archivado" <?php echo $post['estado'] == 'archivado' ? 'selected' : ''; ?>>
                                        Archivado
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="etiquetas" class="form-label">Etiquetas</label>
                                <input type="text" class="form-control" id="etiquetas" name="etiquetas" 
                                       value="<?php echo htmlspecialchars($post['etiquetas'] ?? ''); ?>" 
                                       placeholder="deporte, recreación, salud">
                                <div class="form-text">Separadas por comas</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_descripcion" class="form-label">Descripción Meta</label>
                        <textarea class="form-control" id="meta_descripcion" name="meta_descripcion" 
                                  rows="3" maxlength="160"><?php echo htmlspecialchars($post['meta_descripcion'] ?? ''); ?></textarea>
                        <div class="form-text">
                            Descripción para motores de búsqueda. Máximo 160 caracteres.
                            <span id="meta-count"><?php echo strlen($post['meta_descripcion'] ?? ''); ?></span>/160
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Actualizar Post
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="previewPost()">
                            <i class="bi bi-eye me-2"></i>Vista Previa
                        </button>
                        <a href="?page=manage_posts" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
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
                        <li><strong>Autor:</strong> <?php echo htmlspecialchars($post['autor_nombre'] . ' ' . $post['autor_apellido']); ?></li>
                        <li><strong>Creado:</strong> <?php echo date('d/m/Y H:i', strtotime($post['fecha_creacion'])); ?></li>
                        <?php if ($post['fecha_publicacion']): ?>
                            <li><strong>Publicado:</strong> <?php echo date('d/m/Y H:i', strtotime($post['fecha_publicacion'])); ?></li>
                        <?php endif; ?>
                        <li><strong>Actualizado:</strong> <?php echo date('d/m/Y H:i', strtotime($post['fecha_actualizacion'])); ?></li>
                        <li><strong>Slug:</strong> <code><?php echo htmlspecialchars($post['slug']); ?></code></li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6>Estado actual:</h6>
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
                    <span class="badge bg-<?php echo $status_badges[$post['estado']]; ?> fs-6">
                        <?php echo $status_text[$post['estado']]; ?>
                    </span>
                </div>
                
                <?php if ($post['etiquetas']): ?>
                    <div class="mb-3">
                        <h6>Etiquetas:</h6>
                        <?php
                        $tags = explode(',', $post['etiquetas']);
                        foreach ($tags as $tag): ?>
                            <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tip:</strong> Cambiar el estado a "Publicado" hará el post visible públicamente.
                </div>
                
                <div class="d-grid gap-2">
                    <a href="?page=view_post&id=<?php echo $post_id; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-eye me-2"></i>Ver Post
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="deletePost(<?php echo $post_id; ?>)">
                        <i class="bi bi-trash me-2"></i>Eliminar Post
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Character counter for meta description
document.getElementById('meta_descripcion').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('meta-count').textContent = count;
});

// Preview functionality
function previewPost() {
    const titulo = document.getElementById('titulo').value;
    const contenido = document.getElementById('contenido').value;
    
    if (!titulo || !contenido) {
        alert('Por favor completa el título y contenido antes de previsualizar.');
        return;
    }
    
    // Create a temporary form to submit to preview
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '?page=preview_post';
    form.target = '_blank';
    
    const tituloInput = document.createElement('input');
    tituloInput.type = 'hidden';
    tituloInput.name = 'titulo';
    tituloInput.value = titulo;
    
    const contenidoInput = document.createElement('input');
    contenidoInput.type = 'hidden';
    contenidoInput.name = 'contenido';
    contenidoInput.value = contenido;
    
    form.appendChild(tituloInput);
    form.appendChild(contenidoInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function deletePost(postId) {
    if (confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')) {
        window.location.href = '?page=manage_posts&delete=' + postId;
    }
}
</script> 