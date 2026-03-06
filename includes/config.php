<?php
// includes/config.php

ob_start();
session_start();

// Database connection variables
$db_host = 'localhost';
$db_user = 'root'; // Adjust your DB username
$db_pass = '';     // Adjust your DB password
$db_name = 'sportdeck_db';

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage() . " <br><br><b>Make sure you have created the sportdeck_db database and imported db.sql into MySQL.</b>");
}

// Global configurations
$base_url = '/sportdeck'; // Change this if running from a different subfolder path

// Helper functions (Optional: can be placed here or in a separate functions.php)
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function displayDate($date) {
    return date("M j, Y", strtotime($date));
}

function displayDateTime($datetime) {
    return date("M j, Y - g:i A", strtotime($datetime));
}
?>
