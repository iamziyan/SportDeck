<?php
// admin/users.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = sanitize($_POST['role']);

    if ($user_id == $_SESSION['user_id']) {
        $msg = "<div class='alert alert-error'>You cannot change your own role.</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if($stmt->execute([$new_role, $user_id])){
            $msg = "<div class='alert alert-success'>User role updated successfully.</div>";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    
    <!-- Sidebar -->
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Admin Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Dashboard Overview</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Tournaments</a></li>
            <li><a href="teams.php" class="text-muted" style="display:block; padding:0.5rem;">Teams</a></li>
            <li><a href="players.php" class="text-muted" style="display:block; padding:0.5rem;">Players</a></li>
            <li><a href="matches.php" class="text-muted" style="display:block; padding:0.5rem;">Match Fixtures</a></li>
            <li><a href="results.php" class="text-muted" style="display:block; padding:0.5rem;">Results</a></li>
            <li><a href="users.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">User Roles</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div>
        <div class="flex flex-between mb-2">
            <h1 class="page-title mb-0">Manage User Roles</h1>
        </div>
        
        <?php echo $msg; ?>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered Date</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo displayDate($u['created_at']); ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge badge-blue">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Player</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['id'] !== $_SESSION['user_id']): ?>
                                <form method="POST" action="" style="display:flex; gap:0.5rem;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="role" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; width:100px;">
                                        <option value="player" <?php echo $u['role']=='player'?'selected':''; ?>>Player</option>
                                        <option value="admin" <?php echo $u['role']=='admin'?'selected':''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="update_role" class="btn btn-primary btn-sm">Update</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted text-sm">Cannot edit self</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
