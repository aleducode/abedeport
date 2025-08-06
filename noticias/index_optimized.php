<?php
session_start();
// Include database connection
require_once '../admin/conn.php';

// Enable output buffering and compression
ob_start();
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
}

// Set cache headers for static content
header('Cache-Control: public, max-age=3600'); // 1 hour cache
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// Function to fetch news from database with caching
function getLatestNews($conn, $limit = 6) {
    $cache_key = 'latest_news_' . $limit;
    $cache_file = '/tmp/' . $cache_key . '.cache';
    $cache_duration = 300; // 5 minutes
    
    // Check if cache exists and is still valid
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        return unserialize(file_get_contents($cache_file));
    }
    
    try {
        // Optimized query with proper indexes
        $stmt = $conn->prepare("
            SELECT bp.id_post, bp.titulo, bp.contenido, bp.imagen, bp.slug, 
                   bp.fecha_publicacion, bp.etiquetas, u.nombre, u.apellido 
            FROM blog_posts bp 
            JOIN usuario u ON bp.autor_id = u.id_usuario 
            WHERE bp.estado = 'publicado' 
            ORDER BY bp.fecha_publicacion DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache the result
        file_put_contents($cache_file, serialize($result));
        
        return $result;
    } catch(PDOException $e) {
        error_log("getLatestNews error: " . $e->getMessage());
        return [];
    }
}

// Function to get active tournaments with caching
function getActiveTournaments($conn) {
    $cache_key = 'active_tournaments';
    $cache_file = '/tmp/' . $cache_key . '.cache';
    $cache_duration = 600; // 10 minutes
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        return unserialize(file_get_contents($cache_file));
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT t.id_tournament, t.nombre, t.deporte, t.fecha_inicio,
                   COUNT(et.id_equipo_tournament) as team_count 
            FROM tournaments t 
            LEFT JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
            WHERE t.estado = 'activo' 
            GROUP BY t.id_tournament 
            ORDER BY team_count DESC, t.fecha_inicio DESC
            LIMIT 5
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cache the result
        file_put_contents($cache_file, serialize($result));
        
        return $result;
    } catch(PDOException $e) {
        error_log("getActiveTournaments error: " . $e->getMessage());
        return [];
    }
}

// Utility functions (kept the same but optimized)
function formatSpanishDate($date) {
    static $months = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day de $month, $year";
}

function createSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s_]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function truncateText($text, $length = 150) {
    return strlen($text) <= $length ? $text : substr($text, 0, $length) . '...';
}

function getSportCategories($etiquetas) {
    static $sports = [
        'futbol' => ['name' => 'Fútbol', 'class' => 'category-1'],
        'futsal' => ['name' => 'Futsal', 'class' => 'category-2'], 
        'baloncesto' => ['name' => 'Baloncesto', 'class' => 'category-3'],
        'voleibol' => ['name' => 'Voleibol', 'class' => 'category-4']
    ];
    
    $tags = array_map('trim', explode(',', strtolower($etiquetas)));
    $categories = [];
    
    foreach ($tags as $tag) {
        if (isset($sports[$tag])) {
            $categories[] = $sports[$tag];
        }
    }
    
    return empty($categories) ? [['name' => 'Noticias', 'class' => 'category-1']] : $categories;
}

// Get data with caching
$latestNews = getLatestNews($conn, 6);
$activeTournaments = getActiveTournaments($conn);

// Rest of your HTML content goes here...
// Just replace the original functions with these optimized cached versions
?>