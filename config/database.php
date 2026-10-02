<?php
/**
 * Database Configuration File
 * IMSTS - Integrated Market Surveillance Tracking System
 */

// Check for Render PostgreSQL database URL
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // Parse PostgreSQL connection string from Render
    $parsedUrl = parse_url($databaseUrl);
    $dbHost = $parsedUrl['host'];
    $dbPort = $parsedUrl['port'] ?? 5432;
    $dbName = ltrim($parsedUrl['path'], '/');
    $dbUser = $parsedUrl['user'];
    $dbPass = $parsedUrl['pass'];
    $dbType = 'pgsql';
} else {
    // Local development configuration (MySQL)
    $dbType = 'mysql';
    $dbHost = 'localhost';
    $dbName = 'imsts_db';
    $dbUser = 'root';
    $dbPass = '';
    $dbPort = 3306;
}

try {
    // Create PDO connection based on database type
    if ($dbType === 'pgsql') {
        $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    } else {
        // MySQL for local development
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    }
    
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

/**
 * Execute a prepared statement
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Fetch a single row
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch all rows
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Get last insert ID
 */
function lastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}
?>
