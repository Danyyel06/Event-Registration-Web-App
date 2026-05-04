<?php
date_default_timezone_set('Africa/Lagos');

// Use Railway's environment variables
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'event_reg_db');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

// Optional: Add port (important on Railway)
define('DB_PORT', getenv('MYSQLPORT') ?: 3306);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . 
        ";port=" . DB_PORT . 
        ";dbname=" . DB_NAME . 
        ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>