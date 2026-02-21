<?php
/**
 * =====================================================
 * Sports Tournament Management System - Configuration
 * =====================================================
 * Central configuration file for database connection
 * and application settings.
 * =====================================================
 */

// Prevent direct access to config file
if (!defined('SPORTS_APP')) {
    define('SPORTS_APP', true);
}

// Start session if not already started (required for login/auth)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// Database Configuration
// =====================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sports_tournament_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// Application Settings
// =====================================================
define('APP_NAME', 'Sports Tournament Manager');
define('APP_URL', 'http://localhost/sports');  // Adjust to your local URL
define('BASE_PATH', '');  // Subdirectory path, e.g. '/sports' if in subfolder
define('DEFAULT_TIMEZONE', 'UTC');

// Set default timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

/**
 * Get database connection (PDO)
 * @return PDO Database connection object
 * @throws PDOException On connection failure
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    
    return $pdo;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in - redirects to login if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current logged-in user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Get current user's role (admin, organizer, participant)
 * @return string|null Role or null if not logged in
 */
function getUserRole() {
    $user = getCurrentUser();
    return $user ? $user['role'] : null;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return getUserRole() === 'admin';
}

/**
 * Check if user has at least one of the given roles
 * @param array $roles Allowed roles
 * @return bool
 */
function hasRole(array $roles) {
    $role = getUserRole();
    return $role && in_array($role, $roles);
}

/**
 * Require admin role - redirects to login or user dashboard if not admin
 */
function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Require one of the given roles
 * @param array $roles Allowed roles (admin, organizer, participant)
 */
function requireRole(array $roles) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    if (!hasRole($roles)) {
        header('Location: index.php');
        exit;
    }
}
