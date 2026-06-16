<?php
// 1. Keep the Lagos timezone for your LASU presentation
date_default_timezone_set('Africa/Lagos');

define('DB_HOST', '127.0.0.1:3307');
define('DB_NAME', 'event_registration_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // 4. Important error settings
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    
    die("Database connection failed: " . $e->getMessage());
}
?>