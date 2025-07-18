<?php
// Database check script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Check</h1>";

try {
    include "app/conn.php";
    echo "<p>✅ Database connection established</p>";
    
    // Check if blog_posts table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'blog_posts'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ blog_posts table exists</p>";
        
        // Check table structure
        $stmt = $conn->query("DESCRIBE blog_posts");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check data
        $stmt = $conn->query("SELECT COUNT(*) as count FROM blog_posts");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Total posts: " . $result['count'] . "</p>";
        
        if ($result['count'] > 0) {
            $stmt = $conn->query("SELECT * FROM blog_posts LIMIT 3");
            echo "<h3>Sample Posts:</h3>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<strong>ID:</strong> " . htmlspecialchars($row['id_post']) . "<br>";
                echo "<strong>Title:</strong> " . htmlspecialchars($row['titulo']) . "<br>";
                echo "<strong>Status:</strong> " . htmlspecialchars($row['estado']) . "<br>";
                echo "<strong>Slug:</strong> " . htmlspecialchars($row['slug']) . "<br>";
                echo "<strong>Created:</strong> " . htmlspecialchars($row['fecha_creacion']) . "<br>";
                echo "</div>";
            }
        }
        
        // Check published posts
        $stmt = $conn->query("SELECT COUNT(*) as count FROM blog_posts WHERE estado = 'publicado'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Published posts: " . $result['count'] . "</p>";
        
    } else {
        echo "<p>❌ blog_posts table does not exist</p>";
        echo "<p><a href='install_blog.php'>Run Blog Installation</a></p>";
    }
    
    // Check all tables
    $stmt = $conn->query("SHOW TABLES");
    echo "<h3>All Tables in Database:</h3>";
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='blog/'>Go to Blog</a></p>";
echo "<p><a href='blog/debug.php'>Blog Debug</a></p>";
?> 