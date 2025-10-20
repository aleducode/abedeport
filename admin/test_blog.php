<?php
require_once "conn.php";
include "blog_functions.php";

echo "<h1>Test del Blog en App Folder</h1>";

// Test database connection
echo "<h2>1. Conexión a la base de datos</h2>";
try {
    $test_query = $conn->query("SELECT 1");
    echo "<p style='color: green;'>✓ Conexión exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
}

// Test blog_posts table
echo "<h2>2. Tabla blog_posts</h2>";
try {
    $table_query = $conn->query("SHOW TABLES LIKE 'blog_posts'");
    if ($table_query->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Tabla blog_posts existe</p>";
        
        // Check table structure
        $columns_query = $conn->query("DESCRIBE blog_posts");
        $columns = $columns_query->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Columnas encontradas:</p><ul>";
        foreach ($columns as $column) {
            echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Tabla blog_posts no existe</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al verificar tabla: " . $e->getMessage() . "</p>";
}

// Test blog functions
echo "<h2>3. Funciones del blog</h2>";

// Test getPublishedBlogPosts
try {
    $posts = getPublishedBlogPosts(5);
    echo "<p style='color: green;'>✓ Función getPublishedBlogPosts() funciona</p>";
    echo "<p>Posts publicados encontrados: " . count($posts) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en getPublishedBlogPosts(): " . $e->getMessage() . "</p>";
}

// Test getAllBlogPosts
try {
    $all_posts = getAllBlogPosts(5);
    echo "<p style='color: green;'>✓ Función getAllBlogPosts() funciona</p>";
    echo "<p>Total de posts encontrados: " . count($all_posts) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en getAllBlogPosts(): " . $e->getMessage() . "</p>";
}

// Test file paths
echo "<h2>4. Rutas de archivos</h2>";
$blog_images_dir = "blog_images/";
if (is_dir($blog_images_dir)) {
    echo "<p style='color: green;'>✓ Directorio blog_images existe</p>";
} else {
    echo "<p style='color: orange;'>⚠ Directorio blog_images no existe, creando...</p>";
    if (mkdir($blog_images_dir, 0777, true)) {
        echo "<p style='color: green;'>✓ Directorio blog_images creado</p>";
    } else {
        echo "<p style='color: red;'>✗ Error al crear directorio blog_images</p>";
    }
}

// Test file permissions
if (is_writable($blog_images_dir)) {
    echo "<p style='color: green;'>✓ Directorio blog_images es escribible</p>";
} else {
    echo "<p style='color: red;'>✗ Directorio blog_images no es escribible</p>";
}

// Test blog viewer file
echo "<h2>5. Archivos del blog</h2>";
$blog_files = [
    'blog_viewer.php' => 'Visor público del blog',
    'blog_functions.php' => 'Funciones del blog',
    'admin_dashboard.php' => 'Panel de administración',
    'create_post.php' => 'Crear post',
    'edit_post.php' => 'Editar post',
    'manage_posts.php' => 'Gestionar posts',
    'view_post.php' => 'Ver post individual',
    'preview_post.php' => 'Vista previa de post'
];

foreach ($blog_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file - $description</p>";
    } else {
        echo "<p style='color: red;'>✗ $file - $description (NO EXISTE)</p>";
    }
}

echo "<h2>6. Enlaces de prueba</h2>";
echo "<p><a href='?page=blog_viewer' target='_blank'>Ver Blog Público</a></p>";
echo "<p><a href='?page=admin_dashboard'>Panel de Administración</a></p>";
echo "<p><a href='?page=create_post'>Crear Nuevo Post</a></p>";

echo "<h2>7. Información del sistema</h2>";
echo "<p>Directorio actual: " . getcwd() . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";
echo "<p>Servidor web: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "</p>";
?> 