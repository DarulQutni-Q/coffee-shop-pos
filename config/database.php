<?php
// Database configuration for MySQL
date_default_timezone_set('Asia/Jakarta');

// Load environment variables
function loadEnv($path) {
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

loadEnv(__DIR__ . '/../.env');

// MySQL Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1'); // Force TCP instead of localhost (socket)
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'coffee_shop_pos');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Create connection
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $conn = new PDO($dsn, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Set timezone
            $conn->exec("SET time_zone = '+07:00'");
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Helper function to execute query
function executeQuery($sql, $params = []) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute($params);
        
        return ['success' => true, 'result' => $stmt];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Helper function to fetch all rows
function fetchAll($sql, $params = []) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Helper function to fetch single row
function fetchOne($sql, $params = []) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Helper function to get last insert ID
function getLastInsertId() {
    $conn = getDBConnection();
    return $conn->lastInsertId();
}

// Helper function to escape string (not needed for PDO, but kept for compatibility)
function escapeString($str) {
    return $str;
}

// Test connection on load
try {
    getDBConnection();
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
