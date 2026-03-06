<?php
// logout.php
require_once 'includes/config.php';
session_unset();
session_destroy();
header("Location: " . $base_url . "/login.php");
exit;
?>
