<?php
// admin/matches.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $tournament_id = intval($_POST['tournament_id']);
        $team1_id = intval($_POST['team1_id']);
        $team2_id = intval($_POST['team2_id']);
        $match_date = $_POST['match_date'];
        $venue = sanitize($_POST['venue']);

        if ($team1_id === $team2_id) {
            $msg = "<div class='alert alert-error'>Team 1 and Team 2 cannot be the same.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO matches (tournament_id, team1_id, team2_id, match_date, venue) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$tournament_id, $team1_id, $team2_id, $match_date, $venue])) {
                $msg = "<div class='alert alert-success'>Match fixture created successfully.</div>";
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "<div class='alert alert-success'>Match deleted successfully.</div>";
        }
    }
}

$tournaments = $pdo->query("SELECT id, name FROM tournaments WHERE status != 'completed' ORDER BY id DESC")->fetchAll();
$teams = $pdo->query("SELECT id, name, tournament_id FROM teams ORDER BY name ASC")->fetchAll();

$matches = $pdo->query("
    SELECT m.*, tr.name as tournament_name, t1.name as team1, t2.name as team2 
    FROM matches m 
    JOIN tournaments tr ON m.tournament_id = tr.id 
    JOIN teams t1 ON m.team1_id = t1.id 
    JOIN teams t2 ON m.team2_id = t2.id 
    ORDER BY m.match_date DESC
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
            <li><a href="players.php" class="text-muted" style="display:block; padding:0.5rem; hover:color:var(--primary);">Players</a></li>
            <li><a href="matches.php" style="display:block; padding:0.5rem; background:var(--bg-color); border-radius:4px; font-weight:500;">Match Fixtures</a></li>
            <li><a href="results.php" class="text-muted" style="display:block; padding:0.5rem;">Results</a></li>
            <li><a href="users.php" class="text-muted" style="display:block; padding:0.5rem;">User Roles</a></li>
        </ul>
    </div>
    
    <div>
        <h1 class="page-title mb-2">Match Fixtures</h1>
        <?php echo $msg; ?>

        <div class="card mb-3">
            <h3 class="mb-2">Create Fixture</h3>
            <form method="POST" class="grid grid-2" style="align-items: end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Tournament</label>
                    <select name="tournament_id" id="tournamentSelect" class="form-control" required onchange="filterTeams()">
                        <option value="">-- Select Tournament --</option>
                        <?php foreach($tournaments as $tr): ?>
                            <option value="<?php echo $tr['id']; ?>"><?php echo htmlspecialchars($tr['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Match Date & Time</label>
                    <input type="datetime-local" name="match_date" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Team 1</label>
                    <select name="team1_id" class="form-control team-select" required>
                        <option value="">-- Select Team 1 --</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>" data-tid="<?php echo $t['tournament_id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Team 2</label>
                    <select name="team2_id" class="form-control team-select" required>
                        <option value="">-- Select Team 2 --</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>" data-tid="<?php echo $t['tournament_id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Venue</label>
                    <input type="text" name="venue" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <button type="submit" name="add" class="btn btn-primary w-full">Create Fixture</button>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Date & Time</th><th>Tournament</th><th>Matchup</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($matches as $m): ?>
                    <tr>
                        <td><?php echo displayDateTime($m['match_date']); ?></td>
                        <td><span class="badge badge-gray"><?php echo htmlspecialchars($m['tournament_name']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($m['team1']); ?></strong> vs <strong><?php echo htmlspecialchars($m['team2']); ?></strong></td>
                        <td>
                            <span class="badge <?php echo $m['status']=='completed'?'badge-blue':'badge-orange'; ?>"><?php echo ucfirst($m['status']); ?></span>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this match? If it is completed, results will also be deleted.');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $m['id']; ?>">
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

<script>
function filterTeams() {
    const tid = document.getElementById('tournamentSelect').value;
    document.querySelectorAll('.team-select option').forEach(opt => {
        if(!opt.value) return; // skip placeholder
        if(opt.dataset.tid === tid) opt.style.display = 'block';
        else opt.style.display = 'none';
    });
}
document.addEventListener('DOMContentLoaded', filterTeams);
</script>
<?php include '../includes/footer.php'; ?>
