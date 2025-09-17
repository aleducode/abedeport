<?php
// Test routing for debugging
echo "<h1>Routing Test</h1>";
echo "<p>Current file: " . __FILE__ . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script name: " . $_SERVER['SCRIPT_NAME'] . "</p>";

echo "<h2>Directory Tests:</h2>";
echo "<ul>";
echo "<li><a href='/ejercicio'>/ejercicio</a></li>";
echo "<li><a href='/noticias'>/noticias</a></li>";
echo "<li><a href='/admin'>/admin</a></li>";
echo "</ul>";

echo "<h2>File Existence:</h2>";
echo "<ul>";
echo "<li>ejercicio/index.php: " . (file_exists('ejercicio/index.php') ? 'EXISTS' : 'NOT FOUND') . "</li>";
echo "<li>noticias/index.php: " . (file_exists('noticias/index.php') ? 'EXISTS' : 'NOT FOUND') . "</li>";
echo "<li>admin/index.php: " . (file_exists('admin/index.php') ? 'EXISTS' : 'NOT FOUND') . "</li>";
echo "</ul>";
?>