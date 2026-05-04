<?php
date_default_timezone_set('Africa/Lagos');

// Railway environment variables (these are automatically provided)
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'event_reg_db');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

try {
    // Proper DSN construction
    $dsn = "mysql:host=" . DB_HOST . 
           ";port=" . DB_PORT . 
           ";dbname=" . DB_NAME . 
           ";charset=utf8mb4";

    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ Database Connected Successfully!";

} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>