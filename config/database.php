<?php
/**
 * Database Configuration
 * SMA Al-Kholis Website - PHP Version
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'websekolah_alkholis');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Test database connection
function testConnection() {
    try {
        $pdo = getDBConnection();
        echo "Database connection successful!";
        return true;
    } catch (Exception $e) {
        echo "Database connection failed: " . $e->getMessage();
        return false;
    }
}
?>