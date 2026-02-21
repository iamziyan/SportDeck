<?php
/**
 * =====================================================
 * Header Component - Sports Tournament Management
 * =====================================================
 * Reusable header with navigation for all pages.
 * Includes CSS link and main navigation structure.
 * =====================================================
 */
if (!defined('SPORTS_APP')) {
    require_once __DIR__ . '/../config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?><?php echo APP_NAME; ?></title>
    <!-- Main stylesheet for consistent design across all pages -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">⚽</span>
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </a>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="tournaments.php">Tournaments</a>
                <a href="schedule.php">Schedule</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="nav-admin">Admin Panel</a>
                    <?php endif; ?>
                    <a href="dashboard.php">My Dashboard</a>
                    <a href="teams.php">Teams</a>
                    <a href="matches.php">Matches</a>
                    <a href="results.php">Results</a>
                    <span class="user-menu">
                        <span class="user-name"><?php echo htmlspecialchars(getCurrentUser()['username']); ?> (<?php echo ucfirst(getUserRole()); ?>)</span>
                        <a href="logout.php" class="btn-logout">Logout</a>
                    </span>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn-register">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main-content">
