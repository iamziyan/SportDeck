<?php
// register.php
require_once 'includes/config.php';

if(isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $age = intval($_POST['age']);
    $contact = sanitize($_POST['contact']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = "Email address is already registered.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user (defaults config role to 'player')
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, age, contact, role) VALUES (?, ?, ?, ?, ?, 'player')");
        if($stmt->execute([$name, $email, $hash, $age, $contact])) {
            header("Location: login.php?success=registered");
            exit;
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="card auth-box" style="max-width: 500px;">
    <h2 class="text-center mb-2">Create an Account</h2>
    <p class="text-center text-muted mb-3">Join as a player to view tournaments</p>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required placeholder="John Doe">
        </div>
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6" placeholder="••••••••">
        </div>
        <div class="flex gap-2">
            <div class="form-group" style="flex:1">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" required placeholder="e.g. 21">
            </div>
            <div class="form-group" style="flex:1">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" placeholder="Optional">
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-full mb-2">Register</button>
        <p class="text-center text-muted">Already have an account? <a href="login.php">Sign in</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
