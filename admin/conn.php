<?php
// Database connection configuration using environment variables
$servername = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'db';
$database = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'ABEDEPORT';
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'abedeport_user';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? 'abedeport_strong_password';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Log the error for debugging (in production, don't show detailed errors)
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please check your database configuration.");
} 