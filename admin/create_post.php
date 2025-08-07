<?php
include "blog_functions.php";

$message = '';
$message_type = '';
$redirect_url = '';

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
        $imagen_path = null;
        
        // Handle image upload
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $upload_result = uploadImage($_FILES['imagen']);
            if (isset($upload_result['error'])) {
                $message = $upload_result['error'];
                $message_type = 'danger';
            } else {
                $imagen_path = $upload_result['success'];
            }
        }
        
        if ($message_type != 'danger') {
            $post_data = [
                'titulo' => $titulo,
                'contenido' => $contenido,
                'imagen' => $imagen_path,
                'autor_id' => $data['id_usuario'],
                'estado' => $estado,
                'meta_descripcion' => $meta_descripcion,
                'etiquetas' => $etiquetas
            ];
            
            $post_id = createBlogPost($post_data);
            
            if ($post_id) {
                $message = 'Post creado exitosamente.';
                $message_type = 'success';
                
                // Set redirect URL instead of using header()
                if ($estado == 'publicado') {
                    $redirect_url = "?page=view_post&id=" . $post_id;
                } else {
                    $redirect_url = "?page=edit_post&id=" . $post_id;
                }
            } else {
                $message = 'Error al crear el post.';
                $message_type = 'danger';
            }
        }
    }
}
?>

<?php if ($redirect_url): ?>
<script>
    // JavaScript redirect as fallback when headers can't be sent
    window.location.href = '<?php echo $redirect_url; ?>';
</script>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title mb-0">
                <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Post
            </h1>
            <a href="?page=admin_dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver al Panel
            </a>
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
                    <i class="bi bi-pencil-square me-2"></i>Contenido del Post
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título *</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>" 
                               required maxlength="255">
                        <div class="form-text">Máximo 255 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido *</label>
                        <textarea class="form-control" id="contenido" name="contenido" rows="15" 
                                  required><?php echo htmlspecialchars($_POST['contenido'] ?? ''); ?></textarea>
                        <div class="form-text">
                            Puedes usar HTML básico para formatear el contenido.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen Destacada</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" 
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <div class="form-text">
                            Formatos permitidos: JPG, PNG, GIF. Máximo 5MB.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="borrador" <?php echo ($_POST['estado'] ?? 'borrador') == 'borrador' ? 'selected' : ''; ?>>
                                        Borrador
                                    </option>
                                    <option value="publicado" <?php echo ($_POST['estado'] ?? '') == 'publicado' ? 'selected' : ''; ?>>
                                        Publicado
                                    </option>
                                    <option value="archivado" <?php echo ($_POST['estado'] ?? '') == 'archivado' ? 'selected' : ''; ?>>
                                        Archivado
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Etiquetas</label>
                                <?php 
                                $predefined_tags = getPredefinedTags();
                                $selected_tags = isset($_POST['etiquetas']) ? explode(',', $_POST['etiquetas']) : [];
                                $selected_tags = array_map('trim', $selected_tags);
                                ?>
                                <div class="row">
                                    <?php foreach ($predefined_tags as $key => $label): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="etiquetas_check[]" value="<?php echo $key; ?>" 
                                                       id="tag_<?php echo $key; ?>"
                                                       <?php echo in_array($key, $selected_tags) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="tag_<?php echo $key; ?>">
                                                    <?php echo htmlspecialchars($label); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="etiquetas" id="etiquetas_hidden" value="<?php echo htmlspecialchars($_POST['etiquetas'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_descripcion" class="form-label">Descripción Meta</label>
                        <textarea class="form-control" id="meta_descripcion" name="meta_descripcion" 
                                  rows="3" maxlength="160"><?php echo htmlspecialchars($_POST['meta_descripcion'] ?? ''); ?></textarea>
                        <div class="form-text">
                            Descripción para motores de búsqueda. Máximo 160 caracteres.
                            <span id="meta-count">0</span>/160
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Crear Post
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="previewPost()">
                            <i class="bi bi-eye me-2"></i>Vista Previa
                        </button>
                        <a href="?page=admin_dashboard" class="btn btn-outline-danger">
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
                    <i class="bi bi-info-circle me-2"></i>Información
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Consejos para escribir:</h6>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check text-success me-2"></i>Usa títulos descriptivos</li>
                        <li><i class="bi bi-check text-success me-2"></i>Incluye imágenes relevantes</li>
                        <li><i class="bi bi-check text-success me-2"></i>Estructura el contenido</li>
                        <li><i class="bi bi-check text-success me-2"></i>Usa etiquetas apropiadas</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6>Estados del post:</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-warning me-2">Borrador</span> Solo visible para editores</li>
                        <li><span class="badge bg-success me-2">Publicado</span> Visible públicamente</li>
                        <li><span class="badge bg-secondary me-2">Archivado</span> Oculto pero preservado</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tip:</strong> Puedes guardar como borrador y publicar más tarde.
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

// Handle tag checkboxes
function updateTagsField() {
    const checkboxes = document.querySelectorAll('input[name="etiquetas_check[]"]:checked');
    const selectedTags = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('etiquetas_hidden').value = selectedTags.join(', ');
}

// Add event listeners to all tag checkboxes
document.querySelectorAll('input[name="etiquetas_check[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateTagsField);
});

// Initialize tags field on page load
updateTagsField();

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
</script> 