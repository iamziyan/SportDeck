<?php
// includes/auth.php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    global $base_url;
    if (!isLoggedIn()) {
        header("Location: " . $base_url . "/login.php?error=auth_required");
        exit;
    }
}

function requireAdmin() {
    global $base_url;
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: " . $base_url . "/player/dashboard.php?error=unauthorized");
        exit;
    }
}

function requirePlayer() {
    global $base_url;
    requireLogin();
    if ($_SESSION['role'] !== 'player') {
        header("Location: " . $base_url . "/admin/dashboard.php");
        exit;
    }
}
?>
