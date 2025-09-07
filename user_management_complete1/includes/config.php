<?php
// includes/config.php
// DB configuration - EDIT THESE to match your MySQL credentials
$host = '127.0.0.1';
$db   = 'user_management';
$dbuser = 'root';
$dbpass = ''; // set your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $options);
} catch (PDOException $e) {
    // In production do not echo sensitive info
    exit('Database connection failed: ' . $e->getMessage());
}
?>