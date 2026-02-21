<?php
/**
 * Admin index - Redirects to admin dashboard
 */
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Location: dashboard.php');
exit;
