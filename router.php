<?php
// Simple PHP router for handling directory routing issues
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = rtrim($requestUri, '/');

// Remove query string if present
if (strpos($requestUri, '?') !== false) {
    $requestUri = substr($requestUri, 0, strpos($requestUri, '?'));
}

// Route handling
switch ($requestUri) {
    case '/ejercicio':
        if (file_exists('ejercicio/index.php')) {
            include 'ejercicio/index.php';
            exit;
        }
        break;

    case '/noticias':
        if (file_exists('noticias/index.php')) {
            include 'noticias/index.php';
            exit;
        }
        break;

    case '/admin':
        if (file_exists('admin/index.php')) {
            include 'admin/index.php';
            exit;
        }
        break;

    case '':
    case '/':
        if (file_exists('index.html')) {
            include 'index.html';
            exit;
        }
        break;
}

// If no route matched, return 404
http_response_code(404);
echo "<h1>404 - Page Not Found</h1>";
echo "<p>The requested page could not be found.</p>";
echo "<p>Request URI: " . htmlspecialchars($requestUri) . "</p>";
echo "<p><a href='/'>Go to Home</a></p>";
?>