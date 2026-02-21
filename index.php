<?php
/**
 * =====================================================
 * Home Page - Sports Tournament Management
 * =====================================================
 * Landing page with hero section and feature overview.
 * =====================================================
 */

require_once __DIR__ . '/config.php';

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Manage Sports Tournaments With Ease</h1>
        <p class="hero-subtitle">Create tournaments, manage teams, track matches, and publish results — all in one place.</p>
        <div class="hero-actions">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php" class="btn btn-primary btn-lg">Admin Panel</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg">My Dashboard</a>
                <a href="tournaments.php" class="btn btn-outline btn-lg">Browse Tournaments</a>
            <?php else: ?>
                <a href="tournaments.php" class="btn btn-primary btn-lg">View Tournaments</a>
                <a href="schedule.php" class="btn btn-outline btn-lg">View Schedule</a>
                <a href="register.php" class="btn btn-outline btn-lg">Register</a>
                <a href="login.php" class="btn btn-outline btn-lg">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features">
    <h2>Key Features</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <span class="feature-icon">🏆</span>
            <h3>Tournaments</h3>
            <p>Create and manage multiple tournaments with custom settings.</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">👥</span>
            <h3>Teams</h3>
            <p>Register teams, manage rosters, and track participation.</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">📅</span>
            <h3>Schedule</h3>
            <p>Generate and view match schedules with dates and venues.</p>
        </div>
        <div class="feature-card">
            <span class="feature-icon">📊</span>
            <h3>Results</h3>
            <p>Record match scores and maintain result history.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
