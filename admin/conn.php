<?php
// Database configuration for Docker environment
$servername = getenv('DB_HOST') ?: "db";
$username = getenv('DB_USER') ?: "abedeport_user";
$password = getenv('DB_PASSWORD') ?: "abedeport_password";
$dbABEDEPORT = getenv('DB_NAME') ?: "ABEDEPORT";

$conn = null;

try {
  // Configure PDO options for proper UTF-8 handling
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
  ];

  $conn = new PDO("mysql:host=$servername;dbname=$dbABEDEPORT;charset=utf8mb4", $username, $password, $options);

  // Additional charset configuration
  $conn->exec("SET character_set_client = utf8mb4");
  $conn->exec("SET character_set_connection = utf8mb4");
  $conn->exec("SET character_set_results = utf8mb4");
  $conn->exec("SET collation_connection = utf8mb4_unicode_ci");
} catch(PDOException $e) {
  // Log error instead of echoing to avoid header issues
  error_log("Database connection failed: " . $e->getMessage());
  error_log("Connection details - Host: $servername, User: $username, DB: $dbABEDEPORT");
  // Don't continue execution if database connection fails
  $conn = null;
}

/**
 * Check if user is authenticated and has admin privileges
 * Returns user data if admin, redirects to login if not
 */
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        global $conn;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['usuario'])) {
            header('Location: ./');
            exit();
        }

        // Get user data and check admin status
        $search = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
        $search->bindParam(1, $_SESSION['usuario']);
        $search->execute();
        $data = $search->fetch(PDO::FETCH_ASSOC);

        // Verify user exists and is admin
        if (!is_array($data) || !isset($data['is_admin']) || $data['is_admin'] != 1) {
            // User is not admin - destroy session and redirect
            session_destroy();
            header('Location: ./?error=access_denied');
            exit();
        }

        return $data;
    }
}
?>