<?php
// Database configuration for Docker environment
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
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
  // Don't continue execution if database connection fails
  $conn = null;
}
?>