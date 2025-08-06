<?php
// Database configuration for Docker environment
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
$dbABEDEPORT = getenv('DB_NAME') ?: "ABEDEPORT";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbABEDEPORT;charset=utf8mb4", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // Set charset to ensure proper UTF-8 handling
  $conn->exec("SET NAMES utf8mb4");
  $conn->exec("SET CHARACTER SET utf8mb4");
  $conn->exec("SET character_set_connection=utf8mb4");
} catch(PDOException $e) {
  echo "Conección fallida: " . $e->getMessage();
}
?>