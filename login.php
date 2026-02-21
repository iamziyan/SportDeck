<?php
/**
 * =====================================================
 * Login Module - Sports Tournament Management
 * =====================================================
 * Handles user authentication - displays login form
 * and processes login submissions.
 * Redirects to dashboard on success.
 * =====================================================
 */

require_once __DIR__ . '/config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF (in production, add proper token)
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $pdo = getDbConnection();
            // Fetch user by username or email
            $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                // Redirect admin to admin panel, others to user dashboard
                header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php'));
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
            error_log('Login error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <h1>Welcome Back</h1>
        <p class="auth-subtitle">Sign in to manage your tournaments</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php" class="auth-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                       placeholder="Enter your username or email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
        
        <p class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
