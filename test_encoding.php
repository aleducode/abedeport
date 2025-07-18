<?php
// Test script to verify UTF-8 encoding is working correctly
include "app/conn.php";

echo "<!DOCTYPE html>
<html lang='es-CO'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test de Codificación UTF-8 - AEDEPORT</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-5'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h3>Test de Codificación UTF-8</h3>
            </div>
            <div class='card-body'>";

try {
    // Test 1: Check database connection charset
    $charset_query = $conn->query("SHOW VARIABLES LIKE 'character_set%'");
    echo "<h4>Configuración de Charset de la Base de Datos:</h4>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-sm'>";
    echo "<thead><tr><th>Variable</th><th>Valor</th></tr></thead><tbody>";
    while ($row = $charset_query->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>" . htmlspecialchars($row['Variable_name']) . "</td><td>" . htmlspecialchars($row['Value']) . "</td></tr>";
    }
    echo "</tbody></table></div>";

    // Test 2: Test Spanish words with accents
    echo "<h4>Test de Palabras en Español con Acentos:</h4>";
    $test_words = [
        'fútbol',
        'natación', 
        'recreación',
        'campeones',
        'entrenamiento',
        'deportivo'
    ];
    
    echo "<div class='row'>";
    foreach ($test_words as $word) {
        echo "<div class='col-md-4 mb-2'>";
        echo "<div class='card'>";
        echo "<div class='card-body text-center'>";
        echo "<h5>" . htmlspecialchars($word) . "</h5>";
        echo "<small class='text-muted'>Longitud: " . strlen($word) . " caracteres</small>";
        echo "</div></div></div>";
    }
    echo "</div>";

    // Test 3: Query database for content with accents
    echo "<h4>Contenido de la Base de Datos con Acentos:</h4>";
    $posts_query = $conn->query("SELECT titulo, contenido FROM blog_posts LIMIT 3");
    
    while ($post = $posts_query->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'>";
        echo "<strong>" . htmlspecialchars($post['titulo']) . "</strong>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<p>" . htmlspecialchars(substr($post['contenido'], 0, 200)) . "...</p>";
        echo "</div>";
        echo "</div>";
    }

    echo "<div class='alert alert-success'>";
    echo "<h5>✅ Test Completado</h5>";
    echo "<p>Si puedes ver correctamente las palabras con acentos (fútbol, natación, recreación, etc.), entonces la codificación UTF-8 está funcionando correctamente.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ Error en la Base de Datos</h5>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "
            </div>
            <div class='card-footer'>
                <a href='app/' class='btn btn-primary'>Volver al Panel de Administración</a>
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?> 