<?php
/**
 * =====================================================
 * Registration Module - Sports Tournament Management
 * =====================================================
 * Handles new user registration with form validation.
 * Creates user account and redirects to login on success.
 * =====================================================
 */

require_once __DIR__ . '/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'dashboard.php'));
    exit;
}

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    $errors = [];
    
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($fullName) < 2) {
        $errors[] = 'Full name must be at least 2 characters.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            
            // Check if username or email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Username or email already registered.';
            } else {
                // Hash password and insert user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $email, $passwordHash, $fullName]);
                
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
                // Clear form
                $username = $email = $fullName = '';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error. Please try again.';
            error_log('Registration error: ' . $e->getMessage());
        }
    }
    
    $error = implode('<br>', $errors);
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <h1>Create Account</h1>
        <p class="auth-subtitle">Join to manage and participate in tournaments</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                       placeholder="Choose a username" required minlength="3" autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                       placeholder="Your full name" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Minimum 6 characters" required minlength="6">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" 
                       placeholder="Re-enter password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        
        <p class="auth-footer">
            Already have an account? <a href="login.php">Sign in here</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
