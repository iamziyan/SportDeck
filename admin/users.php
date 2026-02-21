<?php
/**
 * =====================================================
 * Admin Users Module - Sports Tournament Management
 * =====================================================
 * Admin-only: List all users, change roles.
 * =====================================================
 */

require_once __DIR__ . '/../config.php';
requireAdmin();

$pdo = getDbConnection();
$message = '';
$error = '';

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $newRole = $_POST['role'] ?? '';
    $validRoles = ['admin', 'organizer', 'participant'];
    
    if ($userId && in_array($newRole, $validRoles)) {
        // Prevent admin from demoting themselves if they're the only admin
        if ($userId === $_SESSION['user_id'] && $newRole !== 'admin') {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            if ((int) $stmt->fetchColumn() <= 1) {
                $error = 'Cannot change your own role: you are the only admin.';
            }
        }
        if (!$error) {
            try {
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute([$newRole, $userId]);
                $message = 'User role updated.';
            } catch (PDOException $e) {
                $error = 'Failed to update role.';
            }
        }
    }
}

$stmt = $pdo->query('SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/header.php';
?>

<section class="admin-users">
    <h1>Manage Users</h1>
    <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['full_name'] ?? '-'); ?></td>
                        <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <select name="role" onchange="this.form.submit()">
                                    <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="organizer" <?php echo $u['role'] === 'organizer' ? 'selected' : ''; ?>>Organizer</option>
                                    <option value="participant" <?php echo $u['role'] === 'participant' ? 'selected' : ''; ?>>Participant</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
