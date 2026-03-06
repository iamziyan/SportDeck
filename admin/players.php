<?php
// admin/players.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $age = intval($_POST['age']);
        $contact = sanitize($_POST['contact']);
        $team_id = intval($_POST['team_id']);
        $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;

        $stmt = $pdo->prepare("INSERT INTO players (name, age, contact, team_id, user_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $age, $contact, $team_id, $user_id])) {
            $msg = "<div class='alert alert-success'>Player added to roster successfully.</div>";
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM players WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "<div class='alert alert-success'>Player removed from roster successfully.</div>";
        }
    }
}

$teams = $pdo->query("SELECT t.id, t.name, tr.name as t_name FROM teams t JOIN tournaments tr ON t.tournament_id = tr.id ORDER BY t.name ASC")->fetchAll();
$users = $pdo->query("SELECT id, name, email FROM users WHERE role='player' ORDER BY name ASC")->fetchAll();

$players = $pdo->query("
    SELECT p.*, t.name as team_name, tr.name as tournament_name, u.email as account_email
    FROM players p
    JOIN teams t ON p.team_id = t.id
    JOIN tournaments tr ON t.tournament_id = tr.id
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.id DESC
")->fetchAll();

include '../includes/header.php';
?>
<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Admin Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Dashboard Overview</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Tournaments</a></li>
            <li><a href="teams.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Teams</a></li>
            <li><a href="players.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Players</a></li>
            <li><a href="matches.php" class="text-muted" style="display:block; padding:0.5rem;">Match Fixtures</a></li>
            <li><a href="results.php" class="text-muted" style="display:block; padding:0.5rem;">Results</a></li>
            <li><a href="users.php" class="text-muted" style="display:block; padding:0.5rem;">User Roles</a></li>
        </ul>
    </div>
    
    <div>
        <h1 class="page-title mb-2">Tournament Players (Rosters)</h1>
        <?php echo $msg; ?>

        <div class="card mb-3">
            <h3 class="mb-2">Add Player to Team</h3>
            <form method="POST" class="grid grid-3" style="align-items: end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Player Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Age & Contact</label>
                    <div style="display:flex; gap:10px;">
                        <input type="number" name="age" class="form-control" placeholder="Age" style="width:40%">
                        <input type="text" name="contact" class="form-control" placeholder="Contact" style="width:60%">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Team Assignment</label>
                    <select name="team_id" class="form-control" required>
                        <option value="">-- Select Team --</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name'] . ' (' . $t['t_name'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0; grid-column: span 2;">
                    <label class="form-label">Link to User Account (Optional)</label>
                    <select name="user_id" class="form-control">
                        <option value="">-- No Account / Guest Player --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name'] . ' - ' . $u['email']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <button type="submit" name="add" class="btn btn-primary w-full">Add to Roster</button>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Name</th><th>Age/Contact</th><th>Team</th><th>Tournament</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($players as $p): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
                            <?php if($p['account_email']): ?><span class="text-muted text-sm" style="font-size:0.75rem;">Account: <?php echo htmlspecialchars($p['account_email']); ?></span><?php endif; ?>
                        </td>
                        <td><?php echo intval($p['age']) . ' / ' . htmlspecialchars($p['contact']); ?></td>
                        <td><?php echo htmlspecialchars($p['team_name']); ?></td>
                        <td><span class="badge badge-gray"><?php echo htmlspecialchars($p['tournament_name']); ?></span></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Remove player from roster?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
