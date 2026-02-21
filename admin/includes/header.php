<?php
/**
 * =====================================================
 * Admin Header - Sports Tournament Management
 * =====================================================
 * Admin-specific header with admin navigation.
 * Uses parent directory for assets (../assets/).
 * =====================================================
 */
if (!defined('SPORTS_APP')) {
    require_once __DIR__ . '/../../config.php';
}
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>Admin | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <header class="main-header admin-header">
        <div class="header-container">
            <a href="../index.php" class="logo">
                <span class="logo-icon">⚽</span>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </a>
            <nav class="main-nav admin-nav">
                <a href="../index.php">Site Home</a>
                <a href="dashboard.php" class="nav-active">Admin Dashboard</a>
                <a href="users.php">Users</a>
                <a href="../tournaments.php">Tournaments</a>
                <a href="../teams.php">Teams</a>
                <a href="../matches.php">Matches</a>
                <a href="../results.php">Results</a>
                <a href="../dashboard.php">My Dashboard</a>
                <span class="user-menu">
                    <span class="user-name admin-badge"><?php echo htmlspecialchars(getCurrentUser()['username']); ?> (Admin)</span>
                    <a href="../logout.php" class="btn-logout">Logout</a>
                </span>
            </nav>
        </div>
    </header>
    <main class="main-content admin-content">
