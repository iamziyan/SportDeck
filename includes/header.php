<?php // includes/header.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportDeck - Smart Tournament Management</title>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content text-between">
        <a href="<?php echo $base_url; ?>/index.php" class="logo">SportDeck</a>
        
        <ul class="nav-links">
            <li><a href="<?php echo $base_url; ?>/index.php">Tournaments</a></li>
            <li><a href="<?php echo $base_url; ?>/fixtures.php">Fixtures</a></li>
            <li><a href="<?php echo $base_url; ?>/results.php">Results</a></li>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/admin/dashboard.php">Admin Panel</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>/player/dashboard.php">Player Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?php echo $base_url; ?>/logout.php" class="btn btn-primary" style="padding:0.4rem 1rem">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base_url; ?>/login.php" class="btn btn-outline">Login</a></li>
                <li><a href="<?php echo $base_url; ?>/register.php" class="btn btn-primary">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main class="main-content">
