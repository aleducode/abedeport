<?php
require_once "conn.php";

// Require admin authentication and authorization
$data = requireAdmin();

// Predefined tags for blog posts
function getPredefinedTags() {
    return [
        'futbol' => 'Fútbol',
        'futsal' => 'Futsal',
        'baloncesto' => 'Baloncesto',
        'voleibol' => 'Voleibol'
    ];
}

// Function to create URL-friendly slug
function createSlug($string) {
    if (empty($string)) {
        return '';
    }
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Function to check if slug exists
function slugExists($slug, $exclude_id = null) {
    global $conn;
    $sql = "SELECT id_post FROM blog_posts WHERE slug = ?";
    $params = [$slug];
    
    if ($exclude_id) {
        $sql .= " AND id_post != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount() > 0;
}

// Function to generate unique slug
function generateUniqueSlug($title, $exclude_id = null) {
    $base_slug = createSlug($title);
    $slug = $base_slug;
    $counter = 1;
    
    while (slugExists($slug, $exclude_id)) {
        $slug = $base_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

// Function to upload image
function uploadImage($file, $target_dir = "blog_images/") {
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Tipo de archivo no permitido. Solo se permiten JPG, PNG y GIF.'];
    }
    
    if ($file['size'] > $max_size) {
        return ['error' => 'El archivo es demasiado grande. Máximo 5MB.'];
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Return the web-accessible path (admin/blog_images/filename.jpg)
        $web_path = 'admin/' . $target_file;
        return ['success' => $web_path];
    } else {
        return ['error' => 'Error al subir el archivo.'];
    }
}

// Function to get all blog posts
function getAllBlogPosts($limit = null, $offset = null) {
    global $conn;
    
    $sql = "SELECT bp.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
            FROM blog_posts bp 
            JOIN usuario u ON bp.autor_id = u.id_usuario 
            ORDER BY bp.fecha_creacion DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
        if ($offset) {
            $sql .= " OFFSET " . (int)$offset;
        }
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get published blog posts
function getPublishedBlogPosts($limit = null, $offset = null) {
    global $conn;
    
    $sql = "SELECT bp.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
            FROM blog_posts bp 
            JOIN usuario u ON bp.autor_id = u.id_usuario 
            WHERE bp.estado = 'publicado' 
            ORDER BY bp.fecha_publicacion DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
        if ($offset) {
            $sql .= " OFFSET " . (int)$offset;
        }
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get blog post by ID
function getBlogPostById($id) {
    global $conn;
    
    $sql = "SELECT bp.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
            FROM blog_posts bp 
            JOIN usuario u ON bp.autor_id = u.id_usuario 
            WHERE bp.id_post = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get blog post by slug
function getBlogPostBySlug($slug) {
    global $conn;
    
    $sql = "SELECT bp.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
            FROM blog_posts bp 
            JOIN usuario u ON bp.autor_id = u.id_usuario 
            WHERE bp.slug = ? AND bp.estado = 'publicado'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to create blog post
function createBlogPost($data) {
    global $conn;
    
    $slug = generateUniqueSlug($data['titulo']);
    
    $sql = "INSERT INTO blog_posts (titulo, contenido, imagen, autor_id, estado, slug, meta_descripcion, etiquetas, fecha_publicacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $fecha_publicacion = ($data['estado'] == 'publicado') ? date('Y-m-d H:i:s') : null;
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        $data['titulo'],
        $data['contenido'],
        $data['imagen'] ?? null,
        $data['autor_id'],
        $data['estado'],
        $slug,
        $data['meta_descripcion'] ?? null,
        $data['etiquetas'] ?? null,
        $fecha_publicacion
    ]);
    
    return $result ? $conn->lastInsertId() : false;
}

// Function to update blog post
function updateBlogPost($id, $data) {
    global $conn;
    
    $existing_post = getBlogPostById($id);
    if (!$existing_post) {
        return false;
    }
    
    // Handle partial updates - merge with existing data
    if (!isset($data['titulo'])) {
        $data['titulo'] = $existing_post['titulo'];
    }
    if (!isset($data['contenido'])) {
        $data['contenido'] = $existing_post['contenido'];
    }
    
    // Validate required fields
    if (empty($data['titulo'])) {
        return false;
    }
    
    $slug = generateUniqueSlug($data['titulo'], $id);
    
    $sql = "UPDATE blog_posts SET 
            titulo = ?, 
            contenido = ?, 
            imagen = ?, 
            estado = ?, 
            slug = ?, 
            meta_descripcion = ?, 
            etiquetas = ?,
            fecha_publicacion = ?
            WHERE id_post = ?";
    
    $fecha_publicacion = $existing_post['fecha_publicacion'];
    if ($data['estado'] == 'publicado' && $existing_post['estado'] != 'publicado') {
        $fecha_publicacion = date('Y-m-d H:i:s');
    } elseif ($data['estado'] != 'publicado') {
        $fecha_publicacion = null;
    }
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['contenido'],
        $data['imagen'] ?? $existing_post['imagen'],
        $data['estado'],
        $slug,
        $data['meta_descripcion'] ?? null,
        $data['etiquetas'] ?? null,
        $fecha_publicacion,
        $id
    ]);
}

// Function to delete blog post
function deleteBlogPost($id) {
    global $conn;
    
    // Get post info to delete image
    $post = getBlogPostById($id);
    if ($post && $post['imagen']) {
        $image_path = "../" . $post['imagen'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $sql = "DELETE FROM blog_posts WHERE id_post = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$id]);
}

// Function to get post count by status
function getPostCountByStatus($status = null) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count FROM blog_posts";
    if ($status) {
        $sql .= " WHERE estado = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($status) {
        $stmt->execute([$status]);
    } else {
        $stmt->execute();
    }
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}
?> 