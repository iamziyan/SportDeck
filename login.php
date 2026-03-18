<?php
// login.php
require_once 'includes/config.php';

if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] === 'admin') header("Location: " . $base_url . "/admin/dashboard.php");
    else header("Location: " . $base_url . "/player/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: " . $base_url . "/admin/dashboard.php");
        } else {
            header("Location: " . $base_url . "/player/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}

include 'includes/header.php';
?>

<div class="card auth-box">
    <h2 class="text-center mb-2">Welcome Back</h2>
    
    <?php if(isset($_GET['error']) && $_GET['error'] == 'auth_required'): ?>
        <div class="alert alert-error">Please login to access that page.</div>
    <?php endif; ?>
    <?php if(isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
        <div class="alert alert-success">Registration successful. Please login.</div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary w-full mb-2">Sign In</button>
        <p class="text-center text-muted">Don't have an account? <a href="register.php">Register</a></p>
    </form>
    
    <div class="mt-2 text-center text-muted" style="font-size:0.8rem; background: var(--bg-color); padding: 10px; border-radius: 6px;">
        <strong>Demo Admin:</strong> admin@sportdeck.com / 123456
    </div>
</div>

<?php include 'includes/footer.php'; ?>
