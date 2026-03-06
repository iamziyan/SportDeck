<?php
// admin/teams.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $coach = sanitize($_POST['coach']);
        $tournament_id = intval($_POST['tournament_id']);

        $stmt = $pdo->prepare("INSERT INTO teams (name, coach_name, tournament_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $coach, $tournament_id])) {
            $msg = "<div class='alert alert-success'>Team added successfully.</div>";
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "<div class='alert alert-success'>Team deleted successfully.</div>";
        }
    }
}

$tournaments = $pdo->query("SELECT id, name FROM tournaments ORDER BY id DESC")->fetchAll();
$teams = $pdo->query("SELECT t.*, tr.name as tournament_name FROM teams t JOIN tournaments tr ON t.tournament_id = tr.id ORDER BY t.id DESC")->fetchAll();

include '../includes/header.php';
?>
<div class="grid" style="grid-template-columns: 250px 1fr; align-items: start;">
    <div class="card" style="padding: 1rem;">
        <h3 style="font-size:1rem; text-transform:uppercase; color:var(--text-muted); margin-bottom:1rem;">Admin Menu</h3>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
            <li><a href="dashboard.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Dashboard Overview</a></li>
            <li><a href="tournaments.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Tournaments</a></li>
            <li><a href="teams.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Teams</a></li>
            <li><a href="players.php" class="text-muted" style="display:block; padding:0.5rem;">Players</a></li>
            <li><a href="matches.php" class="text-muted" style="display:block; padding:0.5rem;">Match Fixtures</a></li>
            <li><a href="results.php" class="text-muted" style="display:block; padding:0.5rem;">Results</a></li>
            <li><a href="users.php" class="text-muted" style="display:block; padding:0.5rem;">User Roles</a></li>
        </ul>
    </div>
    
    <div>
        <h1 class="page-title mb-2">Teams</h1>
        <?php echo $msg; ?>

        <div class="card mb-3">
            <h3 class="mb-2">Add New Team</h3>
            <form method="POST" class="grid grid-3" style="align-items: end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Team Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Coach Name</label>
                    <input type="text" name="coach" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Tournament</label>
                    <select name="tournament_id" class="form-control" required>
                        <option value="">-- Select Tournament --</option>
                        <?php foreach($tournaments as $tr): ?>
                            <option value="<?php echo $tr['id']; ?>"><?php echo htmlspecialchars($tr['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <button type="submit" name="add" class="btn btn-primary w-full">Add Team</button>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Team Name</th><th>Coach</th><th>Tournament</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($teams as $t): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($t['coach_name']); ?></td>
                        <td><span class="badge badge-gray"><?php echo htmlspecialchars($t['tournament_name']); ?></span></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this team?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $t['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
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
