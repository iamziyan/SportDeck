<?php
/**
 * =====================================================
 * Logout Module - Sports Tournament Management
 * =====================================================
 * Destroys user session and redirects to home page.
 * =====================================================
 */

require_once __DIR__ . '/config.php';

// Destroy all session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// Redirect to home
header('Location: index.php');
exit;
